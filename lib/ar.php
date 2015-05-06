<?php
	if ( !defined('ARBaseDir') ) {
		define('ARBaseDir', dirname( __FILE__ ) . '/ar/');
	}
	require_once(ARBaseDir.'pinp.php');
	require_once(ARBaseDir.'core/exception.php');

	ar_pinp::allow('ar');
	ar_pinp::allow('ar_error');

	class ar implements arKeyValueStoreInterface {
		protected static $instances;
		protected static $ar;
		protected static $context = null;

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
					if (!file_exists(ARBaseDir.$fileName.'.php')) {
						error( $name . ' not found' );
					} else {
						require_once(ARBaseDir.$fileName.'.php');
					}
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
				} else {
					$subFileName = preg_replace( '/[A-Z].*$/', '', $fileName );
					if ( $subFileName != $fileName && file_exists( ARBaseDir.$subFileName.'.php' ) ) {
						require_once( ARBaseDir.$subFileName.'.php' );
					}
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
			$context = self::context();
			$me = $context->getObject();
			if ($me) {
				return $me->call( $template, $params );
			}
		}

		public static function callSuper( $params = null ) {
			$context = self::context();
			$me = $context->getObject();
			if ($me) {
				return $me->_call_super( $params );
			}
		}

		public static function taint(&$value) {
			if ( is_numeric($value) ) {
				return $value;
			} else if ( is_array($value) ) {
				array_walk_recursive( $value, array( 'self', 'taint' ) );
			} else if ( is_string($value) && $value ) { // empty strings don't need tainting
				$value = new arTainted($value);
			}
			return $value;
		}

		public static function untaint(&$value, $filter = FILTER_SANITIZE_SPECIAL_CHARS, $flags = null) {
			if ( $value instanceof arTainted ) {
				$value = filter_var($value->value, $filter, $flags);
			} else if ( is_array($value) ) {
				array_walk_recursive( $value, array( 'self', 'untaintArrayItem'), array(
					'filter' => $filter,
					'flags' => $flags
				) );
			}
			return $value;
		}

		protected static function untaintArrayItem(&$value, $key, $options) {
			self::untaint( $value, $options['filter'], $options['flags'] );
		}

		public function getvar( $name ) {
			global $ARCurrent, $ARConfig;

			if ($ARCurrent->arCallStack) {
				$arCallArgs=end($ARCurrent->arCallStack);
				if ( $name == 'arCallArgs' ) {
					return $arCallArgs;
				}
				if ( isset($arCallArgs[$name]) ) {
					return $arCallArgs[$name];
				}
			} else if ( $name == 'arCallArgs' ) {
				return ar_loader::getvar();
			}
			if ( isset($ARCurrent->$name) ) {
				return $ARCurrent->$name;
			}
			return ar_loader::getvar( $name );
		}

		public function putvar( $name, $value ) {
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

		public static function url( $url ) {
			return new ar_url( $url );
		}

		public static function context() {
			if (!isset(self::$context)) {
				self::setContext( new ar_ariadneContext() );
			}
			return self::$context;
		}

		public static function setContext( $context ) {
			if ( !isset( self::$context ) ) {
				self::$context = $context;
			} else {
				return self::error( 'Context can only be set once.', ar_exceptions::ACCESS_DENIED );
			}
		}

		public static function acquire( $varname, $options = array() ) {
			$context = self::context();
			return $context->acquire( $varname, $options);
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
			if ( $name[0] === '_' ) {
				if ( !method_exists( $this->wrapped, $name ) || !ar_pinp::isAllowed( $this, $name ) ) {
					$name = substr($name, 1);
				}
			}
			if (ar_pinp::isAllowed($this, $name)) {
				try {
					return $this->__wrap( call_user_func_array( array( $this->wrapped, $name), $arguments) );
				} catch( Exception $e ) {
					return ar::error( $e->getMessage(), $e->getCode() );
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

		public function __get( $name ) {
			return $this->wrapped->$name;
		}

		public function __set( $name, $value ) {
			$this->wrapped->$name = $value;
		}

	}


	class ar_error extends ar_exceptionDefault {
		protected static $throwExceptions = false;

		public function __construct( $message = '', $code = 0, $previous = null ) {
			if ( $previous && !($previous instanceof \Exception) ) {
				$previous = new ar_error( $previous );
			}
			parent::__construct( (string) $message, (int) $code, $previous );
			$this->code = $code;
		}

		public function __call($name, $arguments) {
			if (($name[0]==='_')) {
				$realName = substr($name, 1);
				if (ar_pinp::isAllowed($this, $realName)) {
					return call_user_func_array(array($this, $realName), $arguments);
				} else {
					$trace = debug_backtrace(0,2);
					trigger_error("Method $realName not found in class ".get_class($this)." Called from line ".$trace[1]['line']." in ".$trace[1]['file'], E_USER_WARNING);
				}
			} else {
				$trace = debug_backtrace(0,2);
				trigger_error("Method $name not found in class ".get_class($this)." Called from line ".$trace[1]['line']." in ".$trace[1]['file'], E_USER_WARNING);
			}
		}

		public static function isError($ob) {
			return ( is_object($ob)
				&& ( is_a($ob, 'ar_error') || is_a($ob, 'error') || is_a($ob, 'PEAR_Error') ) );
		}

		public static function raiseError($message, $code, $previous = null) {
			if (self::$throwExceptions) {
				throw new ar_error($message, $code, $previous);
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

		public function __toString() {
			return $this->code.": ".$this->message."\r\n";
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

		public static function _callSuper() {
			return ar::callSuper();
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

		public static function _url( $url ) {
			return ar::url( $url );
		}

		public static function _acquire( $varname, $options = array() ) {
			return ar::acquire( $varname, $options );
		}
	}

	function ar($name=null) {
		// this function works as an alternative to statically calling the namespaced class
		// this is a fallback untill namespaces work in php 5.3
		return ar::load($name);
	}

	interface ar_contextInterface {
		public static function getPath( $options = array() );

		public static function getObject( $options = array() );

		public static function getLoader( $options = array() );
	}

	class ar_ariadneContext implements ar_contextInterface {

		public static function makePath( $cwd, $path ) { //FIXME: move this method to a better place
			$result = '/';
			if ( $path[0] === '/' ) {
				$path = substr( $path, 1);
			} else {
				$path = substr( $cwd, 1 ) . '/' . $path;
			}
			if ( $path ) {
				$splitpath = explode( '/', $path );
				foreach ( $splitpath as $pathticle ) {
					switch( $pathticle ) {
						case ".." :
							$result = dirname( $result );
							// if second char of $result is not set, then current result is the rootNode
							if ( isset($result[1]) ) {
								$result .= "/";
							}
							$result[0] = "/"; // make sure that even under windows, slashes are always forward slashes.
						break;
						case "." : break;
						case ""	 : break;
						default:
							$result .= $pathticle . '/';
						break;
					}
				}
			}
			return $result;
		}

		public static function getPath( $options = array() ) {
			$me = self::getObject( $options );
			if ($me) {
				$path = $me->make_path( $options['path'] );
				if ($options['skipShortcuts']) {
					$me->_load('mod_keepurl.php');
					$realpath = pinp_keepurl::_make_real_path( $path );
					if ($realpath) {
						$path = $realpath;
					}
				} else if ($options['rememberShortcuts']) {
					$me->_load('mod_keepurl.php');
					$path = pinp_keepurl::_make_path( $path );
				}
			} else {
				$path = self::makePath( '/', $options['path'] );
			}
			return $path;
		}

		public static function getObject( $options = array() ) {
			if ( class_exists( 'pobject' ) ) {
				$context = pobject::getContext();
				$me = $context["arCurrentObject"];
			} else {
				$me = null;
			}
			return $me;
		}

		public static function getLoader( $options = array() ) { //FIXME: move code from ar_loader to here
			return ar_loader::getLoader();
		}

		public static function acquire( $varname, $options = array() ) {
			$me = self::getObject( $options );
			if ($me) {
				$data = $me->loadUserConfig();
				$vars = explode('.', $varname);
				foreach( $vars as $var ) {
					$data = $data[$var];
				}
				return $data;
			}
		}

		public static function callAtPath( $path, $callback ) {
			$ob = current( ar_store::get($path)->call('system.get.phtml'));
			pobject::pushContext( array(
					"arCurrentObject" => $ob,
					"scope" => "php"
					) );
			call_user_func( $callback );
			pobject::popContext();
		}

	}

	interface arKeyValueStoreInterface {

		public function getvar( $name );

		public function putvar( $name, $value );

	}

	spl_autoload_register('ar::autoload');
