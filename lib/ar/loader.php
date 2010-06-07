<?php
	require_once(dirname(__FILE__).'/../ar.php');
	    

	ar_pinp::allow('ar_loader');

	class ar_loader extends arBase {

		static public $makeLocalURL = false;
		
		public static function configure( $option, $value ) {
			switch ($option) {
				case 'makeLocalURL' :
					self::$makeLocalURL = $value;
				break;
			}
		}
		
		private static function getLoader() {
			global $AR;
			if ($AR->request && isset($AR->request['loader'])) {
				return $AR->request['loader'];
			} else {
				return new ar_core_loader_http();
			}
		}
		
		public static function header( $header ) {
			$loader = self::getLoader();
			return $loader->header( $header );
		}

		public static function redirect( $url ) {
			$loader = self::getLoader();
			return $loader->redirect( $url );
		}

		public static function content( $contentType, $size = 0 ) {
			$loader = self::getLoader();
			return $loader->content( $contentType, $size );
		}

		public static function cache($expires = 0, $modified = false ) {
			$loader = self::getLoader();
			return $loader->cache( $expires, $modified );
		}
		
		public static function disableCache() {
			$loader = self::getLoader();
			return $loader->disableCache();
		}
		
		public static function getvar( $name = null, $method = null ) {
			$loader = self::getLoader();
			return $loader->getvar( $name, $method );
		}

		public static function makeURL( $path = '', $nls = '', $session = true, $https = false ) {
			$loader = self::getLoader();
			return $loader->makeURL( $path, $nls, $session, $https, self::$makeLocalURL );
		}
		
	}
	
?>