<?php

	/* usage
		if ( !$image = ar('cache')->getIfFresh( $naam ) ) {
			if ( ar('cache')->lock( $naam ) ) {
				$image = expensiveOperation();
				ar('cache')->set( $naam, $image, '2 hours' );
			} else {
				// couldn't lock the cache image, so another process is already generating it
				ar('cache')->wait( $naam ); // wait for the lock to be lifted or a timeout
				$image = ar('cache')->get($naam);
			}
		}
		echo $image;
			
	*/

	ar_pinp::allow('ar_cache');
	ar_pinp::allow('ar_cacheStore');
	ar_pinp::allow('ar_cacheWrapper');
	
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
			return new ar_cacheStore( $prefix, $timeout );
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
		
		public static function wrap( $object ) {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return new ar_cacheWrapper( $object, self::$cacheStore );
		}
	}
	
	class ar_cacheWrapper extends arWrapper {
		var $cacheStore = null;
		
		public function __construct( $object, $cacheStore ) {
			parent::__construct( $object );
			$this->cacheStore = $cacheStore;
		}

		protected function __callCatch( $method, $args ) {
			ob_start();
			$result = parent::__call( $method, $args );
			$output = ob_get_contents();
			ob_end_flush();
			return serialize( array(
				'output' => $output,
				'result' => $result
			) );
		}
		
		protected function __callCached( $method, $args ) {
			$path = $method . '(' . md5( $args ) . ')';
			$image = $this->cacheStore->get( $path );
			if ( !$image || !$this->cacheStore->isFresh( $path ) ) {
				$image = $this->__callCatch( $method, $args );
				$this->cacheStore->set( $path, $image );
			} else {
				$image = unserialize( $image );
			}
			return $image;
		}
		
		public function __call( $method, $args ) {
			$image = $this->__callCached( $method, $args );			
			echo $image['output'];
			$result = $image['result'];
			if ( is_object( $result ) ) 
				$result = new ar_cacheWrapper( $result, $this->cacheStore->subStore( $path ) );
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
	
	class ar_cacheStore implements ar_cacheStoreInterface, ar_nameValueStore {
	
		protected $basePath = '';
		protected $timeout = 7200;
		protected $mode = 0777;

		public function __construct( $basePath, $timeout = 7200, $mode = 0777 ) {
			$this->basePath = preg_replace('/\.\./g', '', $basePath);

			if ( is_string($timeout) ) {
				$timeout = strtotime( $timeout, 0);
			}
			$this->timeout = $timeout;
			$this->mode = $mode;

			if ( !defined("ARCacheDir") ) {
				define( "ARCacheDir", sys_get_temp_dir().'ar_cache/' );
			}
			if ( !file_existst("ARCacheDir") ) {
				mkdir( ARCacheDir, $this->mode );
			}
			if ( !file_existst("ARCacheDir") ) {
				throw ar_Exception("Cache Directory does not exist ( ".ARCacheDir." )", 1);
			}
			if ( !is_dir( ARCacheDir ) ) {
				throw ar_Exception("Cache Directory is not a directory ( ".ARCacheDir." )", 1);
			}
			if ( !is_writable( ARCacheDir ) ) {
				throw ar_Exception("Cache Directory is not writable ( ".ARCacheDir." )", 1);
			}
		}
		
		protected function cachePath( $path ) {
			// last '=' is added to prevent conflicts between subdirectories and cache images
			// images always end in a '=', directories never end in a '='
			return ARCacheDir . $basePath . preg_replace('/(\.\.|\=)/g', '', $path) . '='; 
		}
		
		public function subStore( $path ) {
			return new ar_cacheStore( $this->basePath . preg_replace('/(\.\.|\=)/g', '', $path) );
		}
		
		public function get( $path ) {
			$cachePath = $this->cachePath( $path );
			return file_get_contents( $cachePath );
		}
		
		public function isFresh( $path ) {
			$cachePath = $this->cachePath( $path );
			return ( filemtime( $cachePath ) > time() );
		}
		
		public function getIfFresh( $path, $freshness = 0 ) {
			$info = $this->info( $path );
			if ( $info && $info['timeout'] >= $freshness ) {
				return $this->get( $path );
			} else {
				return false;
			}
		}

		public static function lock( $path, $blocking = false ) {
			// locks the file against writing by other processes, so generation of time or resource expensive images
			// will not happen by multiple processes simultaneously
			$cachePath = $this->cachePath( $path );
			$lockFile = fopen( $cachePath, 'c' );
			$lockMode = LOCK_EX;
			if ( !$blocking ) {
				$lockMode = $mode|LOCK_NB;
			}
			return flock( $lockFile, $lockMode );
		}

		public static function wait( $path ) {
			$cachePath = $this->cachePath( $path );
			$lockFile = fopen( $cachePath, 'c' );
			$result = flock( $lockFile, LOCK_EX );
			fclose( $lockFile );
			return $result;
		}

		public function set( $path, $value, $timout = 7200 ) {
			$cachePath = $this->cachePath( $path );
			if ( is_string($timeout) ) {
				$timeout = strtotime( $timeout, 0);
			}
			$dir = dirname( $cachePath );
			if ( !file_exists( $dir ) ) {
				mkdir( $dir, $this->mode, true ); //recursive
			}
			if ( false !== file_put_contents( $cachePath, $value, LOCK_EX ) ) {
				// FIXME: check dat de lock gemaakt met lock() weg is na file_put_contents
				touch( $cachePath, time()+$timeout );
			} else {
				return false;
			}
		}
		
		public function info( $path ) {
			$cachePath = $this->cachePath( $path );
			if ( file_exists( $cachePath ) && is_readable( $cachePath ) ) {
				return array(
					'size' => file_size($cachePath),
					'fresh' => $this->isFresh( $path ),
					'ctime' => filectime( $cachePath ),
					'timeout' => filemtime() - time()
				);
			} else {
				return false;
			}
		}
		
		public function clear( $path = null ) {
			$cachePath = $this->cachePath( $path );
			if ( file_exists( $cachePath ) ) {
				if ( is_dir( $cachePath ) ){
					$cacheDir = dir( $cachePath );
					while (false !== ($entry = $d->read())) {
						if ( $entry != '.' && $entry != '..' ) {
							$this->clear( $path . '/' . $entry ); 
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