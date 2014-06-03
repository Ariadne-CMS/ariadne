<?php

	/* usage

		simple:

		if ( !$image = ar('cache')->getIfFresh( $name ) ) {
			$image = expensiveOperation();
			ar('cache')->set( $naam, $image );
		}
		echo $image;

		with locking:

		if ( !$image = ar('cache')->getIfFresh( $naam ) ) {
			if ( ar('cache')->lock( $naam ) ) {
				$image = expensiveOperation();
				ar('cache')->set( $naam, $image, '2 hours' );
			} else if ( ar('cache')->wait( $naam ) ) { // lock failed, another process is generating the cache 
				// continues here when the lock to be lifted
				$image = ar('cache')->get($naam);
			} else {
				// couldn't lock the file in a reasonable time, you could generate an error here
				// or just go with a stale image, or simply do the calculation:
				$image = expensiveOperation();
			}
		}
		echo $image;

	*/

	ar_pinp::allow('ar_cache');
	ar_pinp::allow('ar_cacheStore');
	ar_pinp::allow('ar_cacheProxy');

	class ar_cache extends arBase {

		static $cacheStore = null;

		public static function config( $options ) {
			if ( $options['cacheStore'] ) {
				self::$cacheStore = $options['cacheStore'];
			}
		}

		public static function create( $prefix = null, $timeout = 7200 ) {
			// this method is used by pinp scripts to create a specific cache
			// so it must be more restrictive than the constructor of the cacheStore
			// which must be able to clear any and all caches
			if ( !$prefix ) { // make sure you have a default prefix, so you won't clear other prefixes unintended
				$prefix = 'default';
			}
			$prefix = 'pinp/'.$prefix; // make sure the pinp scripts have their own top level
			$prefix = $prefix . ar::context()->getPath(); // make sure the cache store is limited to the current path in the context stack
			try {
				return new ar_cacheStore( $prefix, $timeout );
			} catch( Exception $e ) {
				return ar_error::raiseError( $e->getMessage(), $e->getCode() );
			}
		}

		public static function get( $name ) {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return self::$cacheStore->get( $name );
		}

		public static function getIfFresh( $name, $freshness=0 ) {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return self::$cacheStore->getIfFresh( $name, $freshness );
		}

		public static function lock( $name ) {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return self::$cacheStore->lock( $name );
		}

		public static function wait( $name ) {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return self::$cacheStore->wait( $name );
		}

		public static function set( $name, $value, $timeout = 7200 ) {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return self::$cacheStore->set( $name, $value, $timeout );
		}

		public static function info( $name ) {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return self::$cacheStore->info( $name );
		}

		public static function clear( $name = null ) {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return self::$cacheStore->clear( $name );
		}

		public static function purge( $name = null ) {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return self::$cacheStore->purge( $name );
		}

		public static function proxy( $object, $timeout = null ) {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return new ar_cacheProxy( $object, self::$cacheStore, $timeout );
		}

	}

	class ar_cacheProxy extends arWrapper {
		// TODO: allow more control on retrieval:
		// - get contents from cache even though cache may be stale
		//   perhaps through an extra option in __construct?
		var $cacheStore = null;
		var $cacheController = null;
		var $cacheTimeout = '2 hours';

		public function __construct( $object, $cacheStore, $cacheTimeout = null, $cacheController = null ) {
			parent::__construct( $object );
			$this->cacheStore = $cacheStore;
			$this->cacheController = $cacheController;
			if ( isset($cacheTimeout) ) {
				$this->cacheTimeout = $cacheTimeout;
			}
		}

		protected function __callCatch( $method, $args ) {
			ob_start();
			$result = parent::__call( $method, $args );
			$output = ob_get_contents();
			ob_end_clean();
			return array(
				'output' => $output,
				'result' => $result
			);
		}

		protected function __callCached( $method, $args, $path ) {
			if ( !$cacheData = $this->cacheStore->getIfFresh( $path ) ) {
				if ( $this->cacheStore->lock( $path ) ) {
					$cacheData = $this->__callCatch( $method, $args );
					$this->cacheStore->set( $path, $cacheData, $this->cacheTimeout );
				} else if ( $this->cacheStore->wait( $path ) ){
					$cacheData = $this->cacheStore->get( $path );
				} else {
					$cacheData = $this->__callCatch( $method, $args ); // just get the result and return it
				}
			}
			return $cacheData;
		}

		public function __call( $method, $args ) {
			$path = $method . '(' . md5( serialize($args) ) . ')';
			$cacheData = $this->__callCached( $method, $args, $path );
			echo $cacheData['output'];
			$result = $cacheData['result'];
			if ( is_object( $result ) ) {
				$result = new ar_cacheProxy( $result, $this->cacheStore->subStore( $path ) );
			}
			return $result;
		}

		public function __get( $name ) {
			$result = parent::__get( $name );
			if ( is_object( $result ) ) {
				$result = new ar_cacheProxy( $result, $this->cacheStore->subStore( $name ) );
			}
			return $result;
		}

	}

	interface ar_cacheStoreInterface {
		public function get( $path );
		public function set( $path, $value, $timeout = 7200 );
		public function info( $path );
		public function clear( $path = null );
		public function subStore( $path );
		public function isFresh( $path );
	}

	class ar_cacheStore implements ar_cacheStoreInterface, arKeyValueStoreInterface {

		protected $basePath = '';
		protected $timeout = 7200;
		protected $mode = 0777;

		public function __construct( $basePath, $timeout = 7200, $mode = 0777 ) {
			$this->basePath = preg_replace('/\.\./', '', $basePath);

			if ( is_string($timeout) ) {
				$timeout = strtotime( $timeout, 0);
			}
			$this->timeout = $timeout;
			$this->mode = $mode;

			if ( !defined("ARCacheDir") ) {
				define( "ARCacheDir", sys_get_temp_dir().'/ar_cache/' );
			}
			if ( !file_exists( ARCacheDir ) ) {
				mkdir( ARCacheDir, $this->mode );
			}
			if ( !file_exists( ARCacheDir ) ) {
				throw new ar_error("Cache Directory does not exist ( ".ARCacheDir." )", 1);
			}
			if ( !is_dir( ARCacheDir ) ) {
				throw new ar_error("Cache Directory is not a directory ( ".ARCacheDir." )", 1);
			}
			if ( !is_writable( ARCacheDir ) ) {
				throw new ar_error("Cache Directory is not writable ( ".ARCacheDir." )", 1);
			}
		}

		protected function cachePath( $path ) {
			// last '=' is added to prevent conflicts between subdirectories and cache images
			// images always end in a '=', directories never end in a '='
			return ARCacheDir . $this->basePath . preg_replace('/(\.\.|\=)/', '', $path) . '='; 
		}

		public function subStore( $path ) {
			return new ar_cacheStore( $this->basePath . preg_replace('/(\.\.|\=)/', '', $path) );
		}

		public function get( $path ) {
			$cachePath = $this->cachePath( $path );
			if ( file_exists( $cachePath ) ) {
				return unserialize( file_get_contents( $cachePath ) );
			} else {
				return null;
			}
		}

		public function getvar( $name ) {
			return $this->get( $name );
		}

		public function isFresh( $path ) {
			$cachePath = $this->cachePath( $path );
			if ( file_exists( $cachePath ) ) {
				return ( filemtime( $cachePath ) > time() );
			} else {
				return false;
			}
		}

		public function getIfFresh( $path, $freshness = 0 ) {
			$info = $this->info( $path );
			if ( $info && $info['timeout'] >= $freshness ) {
				return $this->get( $path );
			} else {
				return false;
			}
		}

		public function lock( $path, $blocking = false ) {
			// locks the file against writing by other processes, so generation of time or resource expensive images
			// will not happen by multiple processes simultaneously
			$cachePath = $this->cachePath( $path );
			$dir = dirname( $cachePath );
			if ( !file_exists( $dir ) ) {
				mkdir( $dir, $this->mode, true ); //recursive
			}
			$lockFile = fopen( $cachePath, 'c' );
			$lockMode = LOCK_EX;
			if ( !$blocking ) {
				$lockMode = $lockMode|LOCK_NB;
			}
			return flock( $lockFile, $lockMode );
		}

		public function wait( $path ) {
			$cachePath = $this->cachePath( $path );
			$lockFile = fopen( $cachePath, 'c' );
			$result = flock( $lockFile, LOCK_EX );
			fclose( $lockFile );
			return $result;
		}

		public function putvar( $name, $value ) {
			return $this->set( $name, $value );
		}

		public function set( $path, $value, $timeout = null ) {
			$cachePath = $this->cachePath( $path );
			if ( !isset( $timeout ) ) {
				$timeout = $this->timeout;
			}
			if ( is_string( $timeout ) ) {
				$timeout = strtotime( $timeout, 0);
			}
			$dir = dirname( $cachePath );
			if ( !file_exists( $dir ) ) {
				mkdir( $dir, $this->mode, true ); //recursive
			}
			if ( false !== file_put_contents( $cachePath, serialize( $value ), LOCK_EX ) ) {
				// FIXME: check dat de lock gemaakt met lock() weg is na file_put_contents
				touch( $cachePath, time() + $timeout );
			} else {
				return false;
			}
		}

		public function info( $path ) {
			$cachePath = $this->cachePath( $path );
			if ( file_exists( $cachePath ) && is_readable( $cachePath ) ) {
				return array(
					'size' => filesize($cachePath),
					'fresh' => $this->isFresh( $path ),
					'ctime' => filectime( $cachePath ),
					'timeout' => filemtime( $cachePath ) - time()
				);
			} else {
				return false;
			}
		}

		public function clear( $path = null ) {
			$cachePath = $this->cachePath( $path );
			if ( file_exists( $cachePath ) ) {
				return unlink( $cachePath );
			} else {
				return true;
			}
		}

		public function purge( $path = null ) {
			$this->clear( $path );
			$cachePath = substr( $this->cachePath( $path ), 0, -1 ); // remove last '='
			if ( file_exists( $cachePath ) ) {
				if ( is_dir( $cachePath ) ){
					$cacheDir = dir( $cachePath );
					while (false !== ($entry = $cacheDir->read())) {
						if ( $entry != '.' && $entry != '..' ) {
							$this->purge( $path . '/' . $entry ); 
						}
					}
					return rmdir( $cachePath );
				} else {
					return unlink( $cachePath );
				}
			} else {
				return true;
			}
		}
	}

?>