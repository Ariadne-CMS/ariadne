<?php

	class ar_pinp extends arBase {
		private static $allowed;
		
		public static function allow($class, $methods = null) {
			if (isset($methods)) {
				self::$allowed[$class]['methods'] = array_fill_keys($methods, true);
				if (func_num_args()>2) {
					$args = array_slice(func_get_args(), 2);
					self::$allowed[$class]['implements'] = $args;
				}
			} else {
				self::$allowed[$class]['methods'] = true;
			}
		}
		
		public static function allowMatch($class, $methods) {
			self::$allowed[$class]['matches'] = array_fill_keys($methods, true);
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
		
		public static function getCallBack( $method ) {
			if (is_string($method)) {
				return create_function('', '$context = pobject::getContext(); $me = $context["arCurrentObject"]; $me->resetloopcheck(); $args = func_get_args(); return call_user_func_array( array( "ar", "call" ), array( "'.$method.'", $args ) );');
			} else {
				return false;
			}
		}
	}

	ar_pinp::allow('ar_pinp', array('isAllowed', 'getCallBack'));
	
?>