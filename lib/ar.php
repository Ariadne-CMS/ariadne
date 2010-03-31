<?php
	define('ARBaseDir', $AR->dir->install.'/lib/ar/');
	require_once(ARBaseDir.'pinp.php');

	ar_pinp::allow('ar', array('load', 'ls', 'get', 'find', 'parents', 'error'));
	ar_pinp::allow('ar_error');

	class ar {
		protected static $instances;
		protected static $ar;
		
		public static function __callStatic($name, $arguments) {
			return self::load($name);
		}

		public function __call($name, $arguments) {
			return $this->load($name);
		}

		public function __get($name) {
			return $this->load($name);
		}

		private static function _parseClassName($className) {
			$fileName = '';
			if (strpos($className, 'ar_')===0) {
				$fileName = substr($className, 3);
				$fileName = preg_replace('/[^a-z0-9_\.\\\\\/]/i', '', $fileName);
				$fileName = str_replace(array('_','\\'), '/', $fileName);
				$fileName = str_replace('../', '', $fileName);
			}
			return $fileName;
		}
		
		private static function _compileClassName($className) {
			if (strpos($className, 'ar_')!==0) {
				$className = 'ar_'.$className;
			}
			$className = str_replace(array('/','\\'), '_', $className);
			$className = preg_replace('/[^a-z0-9_]/i', '', $className);
			return $className;
		}

		public static function load($name=null) {
			if (!$name) {
				if (!self::$ar) {
					self::$ar = new ar();
				}
				return self::$ar;
			} else {
				$fullName = self::_compileClassName($name);
				if (!class_exists($fullName)) {
					$fileName = self::_parseClassName($fullName);
					require_once(ARBaseDir.$fileName.'.php');
				}
				if (!self::$instances[$name]) {
					self::$instances[$name] = new $fullName();
				}
				return self::$instances[$name];
			}
		}
		
		public static function autoload($className) {
			if (strpos($className, 'pinp_ar_')===0) {
				$className = substr($className, 5);
			}
			if (strpos($className, 'ar_')===0) {
				$fileName = self::_parseClassName($className);
				if (file_exists(ARBaseDir.$fileName.'.php')) {
					require_once(ARBaseDir.$fileName.'.php');
				}
			}
		}
		
		public static function ls() {
			return ar_store::ls();
		}

		public static function find($query="") {
			return ar_store::find($query);
		}

		public static function get($path="") {
			return ar_store::get($path);
		}

		public static function parents($path = ".") {
			return ar_store::parents($path);
		}
		
		public static function error($message, $code) {
			return new ar_error($message, $code);
		}
		
		public static function call( $template, $params = null ) {
			$context = pobject::getContext();
			$me = $context['arCurrentObject'];
			return $me->call( $template, $params );
		}
	}

	class arObject {
		public function __construct( $vars = '' ) {
			if ( is_array($vars) ) {
				foreach ( $vars as $key => $var ) {
					if ( !is_numeric($key) ) {
						$this->{$key} = $var;
					}
				}
			}
		}
	}

	class arBase {		
		public function __call($name, $arguments) {
			if (($name[0]==='_')) {
				$realName = substr($name, 1);
				if (ar_pinp::isAllowed($this, $realName)) {
					return call_user_func_array(array($this, $realName), $arguments);
				} else {
					trigger_error("Method $realName not found in class ".get_class($this), E_USER_ERROR);
				}
			} else {
				trigger_error("Method $realName not found in class ".get_class($this), E_USER_ERROR);
			}
		}
	}

	class ar_error extends arBase {
		var $message;
		var $code;

		public function __construct($message, $code) {
			$this->message = $message;
			$this->code    = $code;
		}

		public static function isError($ob) {
			return (is_a($ob, 'ar_error') || is_a($ob, 'error') || is_a($ob, 'PEAR_Error'));
		}

		public static function raiseError($message, $code) {
			return new ar_error($message, $code);
		}

		public function getMessage() {
			return $this->message;
		}

		public function getCode() {
			return $this->code;
		}
	}

	// FIXME: remove pinp_ar after pinp compiler has no need for it anymore
	class pinp_ar extends ar {
		public static function __callStatic($name, $arguments) {
			return self::load(substr($name, 1), $arguments);
		}
		public function __call($name, $arguments) {
			return $this->load(substr($name, 1), $arguments);
		}
		public function __get($name) {
			return $this->load(substr($name, 1));
		}
		public static function _ls() {
			return ar_store::ls();
		}

		public static function _find($query="") {
			return ar_store::find($query);
		}

		public static function _get($path="") {
			return ar_store::get($path);
		}

		public static function _parents($path = ".") {
			return ar_store::parents($path);
		}

		public static function _error($message, $code) {
			return ar::error($message, $code);
		}
		
		public static function _call($template, $params = null) {
			return ar::call($template, $params);
		}
	}
	
	function ar($name=null) {
		// this function works as an alternative to statically calling the namespaced class
		// this is a fallback untill namespaces work in php 5.3
		return ar::load($name);
	}

	spl_autoload_register('ar::autoload');
?>