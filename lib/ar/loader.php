<?php
	require_once(dirname(__FILE__).'/../ar.php');
	    

	ar_pinp::allow('ar_loader');
	ar_pinp::allow('ar_loaderSession');

	class ar_loader extends arBase {

		static public $makeLocalURL = false;
		static public $session = null;
		
		public static function configure( $option, $value ) {
			switch ($option) {
				case 'makeLocalURL' :
					self::$makeLocalURL = $value;
				break;
			}
		}
		
		public function __set( $name, $value ) {
			ar_loader::configure( $name, $value );
		}
		
		public function __get( $name ) {
			if ( isset( ar_loader::${$name} ) ) {
				return ar_loader::${$name};
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

		public static function makeURL( $path = '', $nls = '', $session = true, $https = null ) {
			$loader = self::getLoader();
			return $loader->makeURL( $path, $nls, $session, $https, self::$makeLocalURL );
		}
		
		public static function session() {
			if (!self::$session) {
				self::$session = new ar_loaderSession();
			}
			return self::$session;
		}
	
	}
	
	class ar_loaderSession extends arBase {
		// FIXME: merge ar_session and ar_loaderSession in some way
		// loader should control only how to get and set the session id
		
		public static function id() {
			global $ARCurrent;
			
			if ($ARCurrent->session) {
				return $ARCurrent->session->id;
			} else {
				return 0;
			}
		}
		
		public static function getvar( $name ) {
			global $ARCurrent;

			if ($ARCurrent->session) {
				return $ARCurrent->session->get($name);
			} else {
				return false;
			}
		}
		
		public static function putvar( $name, $value ) {
			global $ARCurrent;

			if ($ARCurrent->session) {
				return $ARCurrent->session->put($name, $value);
			} else {
				return false;
			}
 		}
	
		public static function start() {
			global $ARCurrent;
			
			ldStartSession(0);
			return $ARCurrent->session->id;
		}
		
		public static function kill() {
		    global $ARCurrent;

			if ($ARCurrent->session) {
				$ARCurrent->session->kill();
				unset($ARCurrent->session);
			} 
		}
	
	}
	
?>