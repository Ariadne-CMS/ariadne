<?php

	ar_pinp::allow('ar_store');
	ar_pinp::allow('ar_storeFind');
	ar_pinp::allow('ar_storeGet');
	ar_pinp::allow('ar_storeParents');

	class ar_store extends arBase {
		static public $rememberShortcuts = true;
		static public $searchObject = false;

		public static function configure( $option, $value ) {
			switch ($option) {
				case 'rememberShortcuts' :
					self::$rememberShortcuts = $value;
				break;
				case 'searchObject' :
					self::$searchObject = $value;
				break;
			}
		}

		public function __set( $name, $value ) {
			ar_store::configure( $name, $value );
		}

		public function __get( $name ) {
			if ( isset( ar_store::${$name} ) ) {
				return ar_store::${$name};
			}
		}

		public static function ls() {
			return new ar_storeList( ar::context()->getPath() );
		}

		public static function find( $query = "" ) {
			return new ar_storeFind( ar::context()->getPath(), $query);
		}

		public static function getSearchPath( $path ) {
			return ar::context()->getPath( array(
				'searchObject' => self::$searchObject,
				'path' => $path
			) );
		}

		public static function getSearchQuery( $query ) {
			return ar::context()->getQuery( $query );
		}

		public static function get( $path = "" ) {
			$path = (string) $path;
			return new ar_storeGet( ar::context()->getPath( array(
				'rememberShortcuts' => self::$rememberShortcuts,
				'path' => $path
			) ) );
		}

		public static function parents() {
			return new ar_storeParents( ar::context()->getPath( array(
				'rememberShortcuts' => self::$rememberShortcuts
			) ) );
		}

		public static function exists( $path = '' ) {
			global $store;
			$path = (string) $path;
			return $store->exists( ar::context()->getPath( array(
				'skipShortcuts' => true,
				'path' => $path
			) ) );
		}

		public static function currentSite( $path = '' ) {
			$path = (string) $path;
			$me = ar::context()->getObject();
			if ($me) {
				if (self::$rememberShortcuts) {
					$me->_load('mod_keepurl.php');
					$path = pinp_keepurl::_currentsite( $path );
				} else {
					$path = $me->currentsite( $path );
				}
			}
			return $path;
		}

		public static function parentSite( $path = '' ) {
			$path = (string) $path;
			$me = ar::context()->getObject();
			if ($me) {
				if (self::$rememberShortcuts) {
					$me->_load('mod_keepurl.php');
					$path = pinp_keepurl::_currentsite( $path.'../' );
				} else {
					$path = $me->parentsite( $path );
				}
			}
			return $path;
		}

		public static function currentSection( $path = '' ) {
			$path = (string) $path;
			$me = ar::context()->getObject();
			if ($me) {
				if (self::$rememberShortcuts) {
					$me->_load('mod_keepurl.php');
					$path = pinp_keepurl::_currentsection( $path );
				} else {
					$path = $me->currentsection( $path );
				}
			}
			return $path;
		}

		public static function parentSection( $path = '' ) {
			$path = (string) $path;
			$me = ar::context()->getObject();
			if ($me) {
				if (self::$rememberShortcuts) {
					$me->_load('mod_keepurl.php');
					$path = pinp_keepurl::_currentsection( $path.'../' );
				} else {
					$path = $me->parentsection( $path );
				}
			}
			return $path;
		}

		public static function currentProject( $path = '' ) {
			$path = (string) $path;
			$me = ar::context()->getObject();
			if ($me) {
				$path = $me->currentproject( $path );
			}
			return $path;
		}

		public static function parentProject( $path = '' ) {
			$path = (string) $path;
			$me = ar::context()->getObject();
			if ($me) {
				$path = $me->parentproject( $path );
			}
			return $path;
		}

		public static function makePath( $path = '' ) {
			$path = (string) $path;
			return ar::context()->getPath( array(
				'rememberShortcuts' => self::$rememberShortcuts,
				'path' => $path
			) );
		}

		public static function makeRealPath( $path = '' ) {
			$path = (string) $path;
			return ar::context()->getPath( array(
				'skipShortcuts' => true,
				'path' => $path
			) );
		}

	}

	class ar_storeFind extends arBase {

		var $limit = 0;
		var $offset = 0;
		var $order = '';
		var $query = '';
		var $path = '/';

		public function __construct( $path = '/', $query = '' ) {
			$this->path = (string)$path;
			$this->query = (string)$query;

			if ( ar_store::$searchObject ) {
				$this->query = ar_store::getSearchQuery( $query );
				$this->path = ar_store::getSearchPath( $path );
			}
		}

		public function call( $template, $args = null ) {
			global $store;
			if ($template instanceof ar_listExpression_Pattern ) {
				$template = ar::listExpression( $this->count() )->pattern( $template );
			}
			if (ar_store::$rememberShortcuts) {
				$path = ar_store::makeRealPath( $this->path );
			} else {
				$path = $this->path;
			}
			$query = $this->query;
			if ($this->order) {
				$query .= ' order by '.$this->order;
			}
			$result = $store->call( $template, $args, $store->find( $path, $query, $this->limit, $this->offset), array( 'usePathAsKey' => true ) );
			return $result;
		}

		public function info() {
			global $store;
			if (ar_store::$rememberShortcuts) {
				$path = ar_store::makeRealPath( $this->path );
			} else {
				$path = $this->path;
			}
			$query = $this->query;
			if ($this->order) {
				$query .= ' order by '.$this->order;
			}
			$result = $store->info( $store->find( $path, $query, $this->limit, $this->offset), array( 'usePathAsKey' => true ));
			return $result;
		}

		public function count() {
			global $store;
			if (ar_store::$rememberShortcuts) {
				$path = ar_store::makeRealPath( $this->path );
			} else {
				$path = $this->path;
			}
			return $store->count_find( $this->path, $this->query, $this->limit, $this->offset );
		}

		public function limit( $limit ) {
			$clone = clone $this;
			$clone->limit = $limit;
			return $clone;
		}

		public function offset( $offset ) {
			$clone = clone $this;
			$clone->offset = $offset;
			return $clone;
		}

		public function order( $order ) {
			$clone = clone $this;
			$clone->order = $order;
			return $clone;
		}

	}

	class ar_storeList extends ar_storeFind {

		public function __construct( $path ) {
			parent::__construct( $path, "object.parent = '" . $path . "'" );
		}

	}

	class ar_storeGet extends arBase {

		protected $path = '';

		public function __construct( $path ) {
			$this->path = (string)$path;
		}

		public function find( $query = "" ) {
			return new ar_storeFind( $this->path, $query );
		}

		public function ls() {
			return new ar_storeList( $this->path );
		}

		public function call( $template, $args = null ) {
			global $store;
			if ( $template instanceof ar_listExpression_Pattern ) {
				$template = ar::listExpression( 1 )->pattern( $template );
			}
			if ( ar_store::$rememberShortcuts ) {
				$path = ar_store::makeRealPath( $this->path );
			} else {
				$path = $this->path;
			}
			return $store->call( $template, $args, $store->get( $path ), array( 'usePathAsKey' => true ) );
		}

		public function info() {
			global $store;
			if ( ar_store::$rememberShortcuts ) {
				$path = ar_store::makeRealPath( $this->path );
			} else {
				$path = $this->path;
			}
			return $store->info( $store->get( $path ), array( 'usePathAsKey' => true ));
		}

		public function parents() {
			return new ar_storeParents( $this->path );
		}

	}

	class ar_storeParents extends arBase {

		protected $path = '';
		protected $top = '/';

		public function __construct( $path = "" ) {
			$this->path	= (string)$path;
		}

		public function call( $template, $args = null ) {
			global $store;
			if ( $template instanceof ar_listExpression_Pattern ) {
				$template = ar::listExpression( $this->count() )->pattern( $template );
			}
			if ( ar_store::$rememberShortcuts) {
				$path     = ar_store::makePath( $this->path );
				$realpath = ar_store::makeRealPath( $this->path );
				if ($realpath != $path ) {
					// must do a call for each seperate path.
					$list   = array();
					$parent = $path;
					while ( $realpath != $this->top && $parent != $this->top && end( $list ) != $realpath ) {
						$list[$parent] = $realpath;
						$parent        = ar_store::makePath( $parent . '../' );
						$realpath      = ar_store::makeRealPath( $parent );
					}
					if ( ( $realpath == $this->top ) || ( $parent == $this->top ) ) {
						$list[$parent] = $realpath;
					}
					$list = array_reverse( $list );
					$result = array();
					foreach ( $list as $virtualpath => $path ) {
						$result[$virtualpath] = current( $store->call( $template, $args,
							$store->get( $path ),
							array(
								'usePathAsKey' => true
							)
						) );
					}
					return $result;
				}
			}
			return $store->call( $template, $args,
				$store->parents( $this->path, $this->top ),
				array( 'usePathAsKey' => true )
			);
		}

		public function info(){
			global $store;

			if ( ar_store::$rememberShortcuts) {
				$path     = ar_store::makePath( $this->path );
				$realpath = ar_store::makeRealPath( $this->path );
				if ($realpath != $path ) {
					// must do a call for each seperate path.
					$list   = array();
					$parent = $path;
					while ( $realpath != $this->top && $parent != $this->top && end( $list ) != $realpath ) {
						$list[$parent] = $realpath;
						$parent        = ar_store::makePath( $parent . '../' );
						$realpath      = ar_store::makeRealPath( $parent );
					}
					if ( ( $realpath == $this->top ) || ( $parent == $this->top ) ) {
						$list[$parent] = $realpath;
					}
					$list = array_reverse( $list );
					$result = array();
					foreach ( $list as $virtualpath => $path ) {
						$result[$virtualpath] = current( $store->info(
							$store->get( $path )
						));
					}
					return $result;
				}
			}

			return $store->info(
				$store->parents( $this->path, $this->top ), array( 'usePathAsKey' => true )
			);
		}

		public function count() {
			global $store;
			return $store->count( $store->parents( $this->path, $this->top ) );
		}

		public function top( $top = "/" ) {
			$clone = clone $this;
			$clone->top = $top;
			return $clone;
		}

	}
