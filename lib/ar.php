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
	
	class ar_object {
		function __construct( $vars = '' ) {
			if ( is_array($vars) ) {
				foreach ( $vars as $key => $var ) {
					if ( !is_numeric($key) ) {
						$this->{$key} = $var;
					}
				}
			}
		}
	}
	
	class ar_base {
		function __call($name, $arguments) {
			if (($name[0]==='_')) {
				$realName = substr($name, 1);
				return call_user_func_array(array($this, $realName), $arguments);
			} else {
				trigger_error("Method $name not found in class ".get_class($this), E_USER_ERROR);
			}
		}
	}
?>