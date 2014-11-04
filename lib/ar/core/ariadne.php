<?php

	interface ar_core_ariadne_interface {
		public function __construct( $ariadneOptions, $storeConfig = null, $arStore = null, $auth = null );
		public function findNearestPath( $path );
		public function handleRequest( $request );
	}

	class ar_core_ariadne implements ar_core_ariadne_interface {
		private $options     = null;
		private $storeConfig = array();
		private $store       = null;
		private $request     = null;
		private $auth        = null;

		public function __construct($ariadneOptions, $store = null, $auth = null) {
			require_once($storeOptions['code'].'stores/'.$store_config['dbms'].'store.phtml');
			$this->options = $ariadneOptions;
			$this->store   = $store;
			$this->auth    = $auth;
		}

		public static function create( $options, $store = null, $auth = null, $cache = null ) {
			if (!isset($auth)) {
				$auth = new ar_core_authentication($options['auth']);
			}
			if ( !isset($cache) ) {
				$cache = new ar_core_cacheDisk( array(
					'cacheDir'   => $options['store']['files'].'cache/',
					'headersDir' => $options['store']['files'].'cacheheaders/'
				) );
			}
			if ( !isset( $store ) ) {
				$store = ar_core_store::create( '/', $options['store'] );
			}
			if ( isset($cache) && $cache ) {
				$ariadne = new ar_core_ariadne_cache( $options['AR'], $store, $auth, $cache );
			} else {
				$ariadne = new ar_core_ariadne( $options['AR'], $store, $auth );
			}
			return $ariadne;
		}

		public function findNearestPath($requestedPath) {
			$path = $requestedPath;
			$prevPath = null;
			while ( $path!=$prevPath && !$this->store->exists($path) ) {
				$prevPath = $path;
				$path     = $this->store->make_path($path, "..");
			}
			if ($path == $prevPath) {
				throw new ar_exception_configError( 'Database is not initialized, please run the Ariadne installer.', ar_exceptions::DATABASE_EMPTY);
			}
			return $path;
		}

		private function storeEnvironment() {
			global $ARCurrent, $AR, $ARConfig, $ARnls, $store;
			array_push(self::$callStack, array( clone($AR), $ARnls, $ARCurrent, $ARConfig, $store ) );
		}

		private function restoreEnvironment() {
			global $ARCurrent, $AR, $ARConfig, $ARnls, $store;
			/* restore original values for backwards compatible globals */
			list ( $AR, $ARnls, $ARCurrent, $ARConfig, $store ) = array_pop(self::$callStack);
		}

		public function handleRequest($request) {
			global $ARCurrent, $AR, $store;
			/* backwards compatibility */
			$this->storeEnvironment();
			$AR          = $this->options;
			$AR->login   = 'public';
			$AR->request = $request;
			$AR->ariadne = $this;
			$store       = $this->store;
			/* end backwards compatibility */
			$this->request = $request;

			unset($this->store->total);

			/* backwards compatibility */
			$rootOptions = '';
			if ( isset($request['session']) && $request['session']->id ) {
				$rootOptions .= '/-' . $request['session']->id . '-';
				$ARCurrent->session = $request['session'];
			}
			if ( isset($request['nls']) && $request['session']->requested ) {
				$rootOptions .= '/' . $request['nls']->requested;
			}
			$this->store->rootoptions = $rootOptions;
			/* end backwards compatibility */

			/* optionally check user id */
			try {

				$user = $request['user'];
				if ( isset($user) && ('public'!=$user) && isset($this->auth) ) {
					$status = $this->auth->checklogin( $user, $request['password'] );
					switch ($status) {
						case LD_ERR_ACCESS :
							throw new ar_exceptionAuthenticationError(
								ar_core_nls::gettext('accessdenied'),
								ar_exceptions::ACCESS_DENIED
							);
						break;
						case LD_ERR_SESSION :
							throw new ar_exceptionAuthenticationError(
								ar_core_nls::gettext('sessiontimeout'),
								ar_exceptions::SESSION_TIMEOUT
							);
						break;
						case LD_ERR_EXPIRED :
							throw new ar_exceptionAuthenticationError(
								ar_core_nls::gettext('sessionpasswordexpired'),
								ar_exceptions::PASSWORD_EXPIRED
							);
						break;
					}
				}

				/* FIXME: move call to ar_core_ariadne */
				$this->store->call($request['template'], $request['params'],
					$this->store->get( $request['path'] ) );

				$this->restoreEnvironment();

			} catch( exception $e ) {

				$this->restoreEnvironment();
				throw $e;

			}
			if (!$this->store->total) {
				throw new ar_exception_default( 'Object not found', ar_exceptions::OBJECT_NOT_FOUND);
			}
		}

	}

	class ar_core_ariadneCache extends ar_core_ariadne {
		private $cache = null;

		public function __construct($ariadneOptions, $arStore = null, $auth = null, $cache = null) {
			$this->cache          = $cache;
			parent::__construct($ariadneOptions, $arStore, $auth);
		}

		public function handleRequest($request) {
			$loader     = $request['loader'];
			$cacheImage = $this->cache->getCacheFilename($request);
			if ( isset($request['cache']) && $request['cache'] && $this->isFresh($cacheImage) ) {
				$cacheControl = $request['cache'];
				if ( is_array($cacheControl) ) {
					if ( $ifModifiedSince = $cacheControl['if-modified-since']
						&& strtotime($ifModifiedSince) >= $this->cache->getCreatedTime($cacheImage) )
					{
						$loader->header('HTTP/1.1 304 Not Modified');
						$loader->header('Expires: '.gmdate(DATE_RFC1123,
							$this->cache->getStaleTime($cacheImage) ) );
						return;
					} else if ( ( !isset($cacheControl['max-age'])
								|| ( $maxAge = $cacheControl['max-age']
								&& $this->cache->isYounger($cacheImage, $maxAge)	)
							) && ( !isset($cacheControl['min-fresh'])
								|| ( $minFresh = $cacheControl['min-fresh']
								&& !$this->cache->isFresher($cacheImage, $minFresh) )
						) )
					{
						echo $this->cache->headers($cacheImage);
						$this->cache->passThru($cacheImage);
						return;
					}
				}
			}
			// check only-if-cache here
			// check if we may cache the request result if so start the cache
			$this->cache->start();
			parent::handleRequest($request);
			// check if we may still cache the result
			if ($this->cache->enabled) {
				$this->cache->save($cacheImage, $this->cache->freshness);
			}
		}
	}
