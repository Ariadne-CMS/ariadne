<?php
	define(arBaseDir, $AR->dir->install.'/lib/ar/');

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
		
		public static function load($name=null) {
			if (!$name) {
				if (!self::$ar) {
					self::$ar = new ar();
				}
				return self::$ar;
			} else {
				$fullName = 'ar_'.$name;
				if (!class_exists($fullName)) {
					require_once('./'.$name.'.php');
				}
				if (!self::$instances[$name]) {
					self::$instances[$name] = new $fullName();
				}
				return self::$instances[$name];
			}
		}
		
		public static function autoload($className) {
			if (strpos($className, 'ar_')===0) {
				$fileName = substr($className, 3);
				$fileName = preg_replace('/[^a-z0-9_\-\.]/i', '', $fileName);
				$fileName = str_replace('_', '/', $fileName);
				$fileName = str_replace('../', '', $fileName);
				if (file_exists(arBaseDir.$fileName.'.php')) {
					require_once(arBaseDir.$fileName.'.php');
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
			return new arError($message, $code);
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
	
	abstract class arBase {
		protected static $_pinp_export = array();
		
		public function __call($name, $arguments) { // FIXME; remove this method after pinp compiler is patched
			if (($name[0]==='_')) {
				$realName = substr($name, 1);
				if (self::_pinp_is_allowed($realName)) {
					return call_user_func_array(array($this, $realName), $arguments);
				} else {
					trigger_error("Method $realName not found in class ".get_class($this), E_USER_ERROR);
				}
			} else {
				trigger_error("Method $realName not found in class ".get_class($this), E_USER_ERROR);
			}
		}
		
		public static function _pinp_is_allowed($method) {
			return in_array(self::$_pinp_export, $method);
		}
		

	}

	class arError extends arBase {
		var $message;
		var $code;

		public function error($message, $code) {
			$this->message=$message;
			$this->code=$code;
		}

		public function isError($ob) {
			return (is_a($ob, 'arError') || is_a($ob, 'error') || is_a($ob, 'PEAR_Error'));
		}

		public function raiseError($message, $code) {
			return new error($message, $code);
		}

		public function getMessage() {
			return $this->message;
		}

		public function getCode() {
			return $this->code;
		}
	}
	
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
	}
	
	function ar($name=null) {
		return ar::load($name);
	}

	spl_autoload_register('ar::autoload');
?>