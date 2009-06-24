<?php

	class ar_pinp {
		private static $allowed;

		public static function allow($class, $methods) {
			self::$allowed[$class]['methods'] = array_fill_keys($methods, true);
			if (func_num_args()>2) {
				$args = array_slice(func_get_args(), 2);
				self::$allowed[$class]['implements'] = $args;
			}				
		}

		public static function isAllowed($class, $method) {
			// FIXME: support interfaces?
			if (!is_string($class)) {
				$class = get_class($class);
			}
			do {
				if (self::$allowed[$class]['methods'][$method]) {
					return true;
				} 
			} while ($class = get_parent_class($class));
			return false;
		}
	}
	
?>