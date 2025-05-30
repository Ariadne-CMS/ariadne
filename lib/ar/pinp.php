<?php
	class ar_pinp extends arBase {

		private static $allowed;

		public static function allow($class, $methods = null) {
			if (isset($methods)) {
				self::$allowed[$class]['methods'] = array_combine( $methods, array_fill( 0, count($methods), true ) );
				if (func_num_args()>2) {
					$args = array_slice(func_get_args(), 2);
					self::$allowed[$class]['implements'] = $args;
				}
			} else {
				self::$allowed[$class]['methods'] = true;
			}
		}

		public static function allowMatch($class, $methods) {
			self::$allowed[$class]['matches'] = array_combine( $methods, array_fill( 0, count($methods), true ) );
		}

		public static function isAllowed($class, $method=null) {
			// FIXME: support interfaces?
			if (!is_string($class)) {
				$class = get_class($class);
			}
// This breaks the demo site;
//			if (!isset(self::$allowed[$class])) {
//				return false;
//			}
			if ($method===null && self::$allowed[$class]) { // only accept original class here
				return true;
			}
			$current = $class;
			do {
				if (isset(self::$allowed[$current]) ) {
					if (self::$allowed[$current]['methods']===true) {
						return true;
					} else if (isset(self::$allowed[$current]['methods'][$method])
						&& self::$allowed[$current]['methods'][$method]) {
						return true;
					} else if (isset(self::$allowed[$current]['matches'])) {
						$result = preg_match( '/(' . implode ( '|', self::$allowed[$current]['matches']) . ')/is',  $method );
						if ($result) {
							return true;
						}
					}
				}
			} while ($current = get_parent_class($current));
			return false;
		}

		public static function construct($className, $args = array() ) {
			if (self::isAllowed($className)) {
				foreach ($args as $key => $value) {
					if ($value instanceOf arWrapper) {
						$args[$key] = $value->__unwrap();
					}
				}

				// return new $className(...$args);
				return new arWrapper( new $className(...$args) );
			} else {
				throw new Exception("$className is not allowed");
			}
		}

		public static function callStatic($className, $method, $args = array() ) {
			if (self::isAllowed($className)) {
				foreach ($args as $key => $value) {
					if ($value instanceOf arWrapper) {
						$args[$key] = $value->__unwrap();
					}
				}
				return new arWrapper( call_user_func(array($className, $method), ...$args) );
			} else {
				throw new Exception("$className::$method is not allowed");
			}
		}

		public static function getCallback( $method, $params = array() ) {
			if (is_string($method)) {
				return function() use ( $params, $method ) {
					$me = ar::context()->getObject();
					$args_in = func_get_args();
					if (count($params)) {
						$args = array();
						foreach($params as $key => $arg) {
							$args[$arg] = $args_in[$key];
						}
					} else {
						$args = $args_in;
					}
					return ar::call($method, $args);
				};
			} else {
				return false;
			}
		}

		public static function load( $name, $library ) {
			$context = ar::context()->getObject();
			return $context->loadLibrary( $name, $library );
		}

		public static function loaded( $name = null, $library = null ) {
			global $ARConfig;
			// return if a library with that name is loaded
			// if library is set will also check if it is the same path
			$context = ar::context()->getObject();
			$path = $context->path;
			$config = $ARConfig->cache[$path] ?: $context->loadConfig($path);
			if ( isset($name) ) {
				$libraryPath = $config->libraries[$name] ?: null;
				if ( isset($library) ) {
					return ($libraryPath == $library );
				} else {
					return $libraryPath;
				}
			} else {
				return $config->libraries;
			}
		}

		public static function exists( $template ) {
			// will check if a template with the given name is available to call
			// template has format "library:type::function"
			// FIXME: allow search for specific nls as well
			$context = ar::context()->getObject();
			$templateData = $context->getPinpTemplate($template);
			if ( !$templateData || !$templateData['arTemplateId'] ) {
				return false;
			} else {
				return $templateData;
			}
		}
	}

	ar_pinp::allow('ar_pinp', array('isAllowed', 'getCallback', 'load', 'loaded', 'exists', 'new'));
