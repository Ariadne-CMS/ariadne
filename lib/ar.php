<?php
	define('ARBaseDir', $AR->dir->install.'/lib/ar/');
	require_once(ARBaseDir.'pinp.php');
	require_once(ARBaseDir.'core/exception.php');

	ar_pinp::allow('ar', array('load', 'ls', 'get', 'find', 'parents', 'error', 'getvar', 'call', 'taint', 'untaint'));
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
		
		public static function exists($path = '.') {
			return ar_store::exists($path);
		}
		
		public static function error($message, $code, $previous = null) {
			return ar_error::raiseError($message, $code, $previous);
		}
		
		public static function call( $template, $params = null ) {
			$context = pobject::getContext();
			$me = $context['arCurrentObject'];
			return $me->call( $template, $params );
		}
		
		public static function taint(&$value) {
			if ( is_numeric($value) ) {
				return;
			} else if ( is_array($value) ) {
				array_walk_recursive( $value, array( self, 'taint' ) );
			} else if ( is_string($value) && $value ) { // empty strings don't need tainting
				$value = new arTainted($value);
			}
		}

		public static function untaint(&$value, $filter = FILTER_SANITIZE_SPECIAL_CHARS, $flags = null) {
			if ( $value instanceof arTainted ) {
				$value = filter_var($value->value, $filter, $flags);
			} else if ( is_array($value) ) {
				array_walk_recursive( $value, array( self, 'untaintArrayItem'), array( 
					'filter' => $filter,
					'flags' => $flags
				) );
			}
		}
		
		protected static function untaintArrayItem(&$value, $key, $options) {
			self::untaint( $value, $options['filter'], $options['flags'] );
		}

		public static function getvar( $name ) {
			global $ARCurrent, $ARConfig;
			
			if ($ARCurrent->arCallStack) {
				$arCallArgs=end($ARCurrent->arCallStack);
				if ( isset($arCallArgs[$name]) ) {
					return $arCallArgs[$name];
				}
			}
			$context = pobject::getContext();
			if ( is_array($context) ) {
				$me = $context["arCurrentObject"];
			}
			if ( isset($ARCurrent->$name) ) {
				return $ARCurrent->$name;
			} else if ( isset($me) && isset($ARConfig->pinpcache[$me->path][$name])) {
				return $ARConfig->pinpcache[$me->path][$name];
			}
			return ar_loader::getvar( $name );
		}
		
		public static function putvar( $name, $value ) {
			global $ARCurrent;
			$ARCurrent->$name = $value;
		}
		
		public static function listExpression( $list ) {
			return new ar_listExpression( $list );
		}
		
		public static function listPattern() {
			self::autoload('ar_listExpression');
			$params = func_get_args();
			return new ar_listExpression_Pattern( $params );
		}
	}
	
	class arTainted {
		public $value = null;

		public function __construct($value) {
			$this->value = $value;
		}

		public function __toString() {
			return filter_var($this->value, FILTER_SANITIZE_SPECIAL_CHARS);
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
					trigger_error("Method $realName not found in class ".get_class($this), E_USER_WARNING);
				}
			} else {
				trigger_error("Method $name not found in class ".get_class($this), E_USER_WARNING);
			}
		}
	}

	class arWrapper {
	
		protected $wrapped = null;
		protected $__class = 'arWrapper';
		
		public function __construct( $wrapped ) {
			$this->wrapped = $wrapped;
		}
		
		public function __call($name, $arguments) {
			if (($name[0]==='_')) {
				$realName = substr($name, 1);
				if (ar_pinp::isAllowed($this, $realName)) {
					try {
						return $this->__wrap( call_user_func_array(array($this->wrapped, $realName), $arguments) );
					} catch( Exception $e ) {
						return ar::error( $e->getMessage(), $e->getCode() );
					}
				} else {
					trigger_error("Method $realName not found in class ".get_class($this), E_USER_WARNING);
				}
			} else {
				trigger_error("Method $name not found in class ".get_class($this), E_USER_WARNING);
			}
		}
		
		public function __wrap( $result ) {
			if (is_object($result)) {
				$class = $this->__class;
				return new $class($result);
			} else {
				return $result;
			}
		}		
	}

	
	class ar_error extends arBase {
		var $message;
		var $code;
		static $throwExceptions = false;

		public function __construct($message = null, $code = null, $previous = null) {
			$this->message  = $message;
			$this->code     = $code;
			$this->previous = $previous;
		}

		public static function isError($ob) {
			return (is_a($ob, 'ar_error') || is_a($ob, 'error') || is_a($ob, 'PEAR_Error'));
		}

		public static function raiseError($message, $code, $previous) {
			if (self::$throwExceptions) {
				throw new ar_exceptionDefault($message, $code, $previous);
			} else {
				return new ar_error($message, $code, $previous);
			}
		}

		public static function configure( $option, $value ) {
			switch ($option) {
				case 'throwExceptions' : 
					self::$throwExceptions = $value;
				break;
			}
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

		public static function _exists($path = '.') {
			return ar_store::exists($path);
		}
		
		public static function _error($message, $code) {
			return ar::error($message, $code);
		}
		
		public static function _call($template, $params = null) {
			return ar::call($template, $params);
		}
		
		public static function _taint(&$value) {
			ar::taint($value);
		}
		
		public static function _untaint(&$value, $filter = FILTER_SANITIZE_SPECIAL_CHARS, $flags = null) {
			ar::untaint($value, $filter, $flags);
		}
		
		public static function _getvar( $name ) {
			return ar::getvar( $name );
		}
		
		public static function _putvar( $name, $value ) {
			return ar::putvar( $name, $value );
		}
		
		public static function _listExpression( $list ) {
			return ar::listExpression( $list );
		}
		
		public static function _listPattern() {
			$params = func_get_args();
			return call_user_func_array( array( 'ar', 'listPattern'), $params);
		}
	}
	
	function ar($name=null) {
		// this function works as an alternative to statically calling the namespaced class
		// this is a fallback untill namespaces work in php 5.3
		return ar::load($name);
	}

	spl_autoload_register('ar::autoload');
?>