<?php
	ar_pinp::allow('ar_loader');

	class ar_loader extends arBase {
		
		public static function header( $header ) {
			ldHeader( $header );
		}

		public static function redirect( $url ) {
			ldRedirect( $url );
		}

		public static function setContent( $contentType, $size = 0 ) {
			ldSetContent( $contentType, $size );
		}

		public static function setClientCache( $cacheEnabled, $expires = 0, $modified = 0 ) {
			ldSetClientCache( $cacheEnabled, $expires, $modified );
		}
		
		public static function getvar( $name ) {
			global $AR;
			if ($AR->request && isset($AR->request['loader'])) {
				$loader = $AR->request['loader'];
				return $loader->getvar( $name );
			} else {
				return ar_http::getvar( $name );
			}
		}

	}
	
?>