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

		public static function isAllowed($class, $method) {
			// FIXME: support interfaces?
			if (!is_string($class)) {
				$class = get_class($class);
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

		public static function loaded( $name, $library = null ) {
			global $ARConfig;
			// return if a library with that name is loaded
			// if library is set will also check if it is the same path
			$context = ar::context()->getObject();
			$path = $context->path;
			$config = ($ARConfig->cache[$path]) ? $ARConfig->cache[$path] : $context->loadConfig($path);
            $libraryPath = $config->libraries[$name] ?: null;
			if ( isset($library) ) {
				return ($libraryPath == $library );
			} else {
				return $libraryPath;
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

	ar_pinp::allow('ar_pinp', array('isAllowed', 'getCallback', 'load', 'loaded', 'exists'));
