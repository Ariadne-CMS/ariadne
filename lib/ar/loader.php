<?php
	require_once(dirname(__FILE__).'/../ar.php');
	    

	ar_pinp::allow('ar_loader');

	class ar_loader extends arBase {

		private static function getLoader() {
			global $AR;
			if ($AR->request && isset($AR->request['loader'])) {
				return $AR->request['loader'];
			} else {
				return new ar_http(); // FIXME: change to ar_core_loader_http when implemented
			}
		}
		
		public static function header( $header ) {
			return ldHeader( $header );
		}

		public static function redirect( $url ) {
			return ldRedirect( $url );
		}

		public static function content( $contentType, $size = 0 ) {
			return ldSetContent( $contentType, $size );
		}

		public static function cache($expires = 0, $modified = 0 ) {
			return ldSetClientCache( true, $expires, $modified );
		}
		
		public static function disableCache() {
			return ldSetClientCache( false );
		}
		
		public static function getvar( $name ) {
			$loader = self::getLoader();
			return $loader->getvar( $name );
		}

	}
	
?>