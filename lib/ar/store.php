<?php
	
	require_once(dirname(__FILE__).'/../ar.php');

	ar_pinp::allow('ar_store');

	ar_pinp::allow('ar_storeFind'); 

	ar_pinp::allow('ar_storeGet');

	ar_pinp::allow('ar_storeParents');

	class ar_store extends arBase {
		static public $rememberShortcuts = true;
		
		public static function configure( $option, $value ) {
			switch ($option) {
				case 'rememberShortcuts' :
					self::$rememberShortcuts = $value;
				break;
			}
		}
				
		public static function ls() {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return new ar_storeList($me->path);
		}

		public static function find($query="") {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return new ar_storeFind($me->path, $query);
		}

		public static function get($path="") {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (self::$rememberShortcuts) {
				$me->_load('mod_keepurl.php');
				//self::$rememberShortcuts = false;
				//$path = self::makePath( $path );
				//self::$rememberShortcuts = true;
				$path = pinp_keepurl::_make_path( $path );
			} else {
				$path = $me->make_path( $path );
			}
			return new ar_storeGet($path);
		}

		public static function parents() {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$path = $me->path;
			if (self::$rememberShortcuts) {
				$me->_load('mod_keepurl.php');
				$path = pinp_keepurl::_make_path( $path );
			}
			return new ar_storeParents($path);
		}
		
		public static function exists($path = ".") {
			global $store;
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (self::$rememberShortcuts) {
				$me->_load('mod_keepurl.php');
				$path = pinp_keepurl::_make_real_path( $path );
			} else {
				$path = $store->make_path($me->path, $path);
			}
			return $store->exists($path);
		}

		public static function currentSite() {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (self::$rememberShortcuts) {
				$me->_load('mod_keepurl.php');
				$path = pinp_keepurl::_currentsite( $path );
			} else {
				$path = $me->currentsite( $path );
			}
			return $path;
		}
		
		public static function parentSite( $site ) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (self::$rememberShortcuts) {
				$me->_load('mod_keepurl.php');
				$path = pinp_keepurl::_currentsite( $path.'../' );
			} else {
				$path = $me->parentsite( $path );
			}
			return $path;
		}
		
		public static function currentSection( $path = '' ) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (self::$rememberShortcuts) {
				$me->_load('mod_keepurl.php');
				$path = pinp_keepurl::_currentsection( $path );
			} else {
				$path = $me->currentsection( $path );
			}
			return $path;
		}

		public static function parentSection( $path = '' ) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (self::$rememberShortcuts) {
				$me->_load('mod_keepurl.php');
				$path = pinp_keepurl::_currentsection( $path.'../' );
			} else {
				$path = $me->parentsection( $path );
			}
			return $path;
		}

		public static function makePath( $path = '' ) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (self::$rememberShortcuts) {
				$me->_load('mod_keepurl.php');
				$path = pinp_keepurl::_make_path( $path );
			} else {
				$path = $me->make_path( $path );
			}
			return $path;
		}
		
		public static function makeRealPath( $path = '' ) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$me->_load('mod_keepurl.php');
			$path = pinp_keepurl::_make_real_path( $path );
			return $path;
		}

	}

	class ar_storeFind extends arBase {

		var $limit = 0;
		var $offset = 0;
		var $order = '';
		var $query = '';
		var $path = '/';

		public function __construct($path='/', $query='') {
			$this->path = $path;
			$this->query = $query;
		}

		public function call($template, $args=null) {
			global $store;
			if ($template instanceof ar_listExpression_Pattern ) {
				$template = ar::listExpression( $this->count() )->pattern( $template );
			}
			if (ar_store::$rememberShortcuts) {
				$path = ar_store::makeRealPath( $this->path );
			} else {
				$path = $this->path;
			}
			$result = $store->call($template, $args, $store->find($path, $this->query, $this->limit, $this->offset), array( 'usePathAsKey' => true ) );
			return $result;
		}

		public function count() {
			global $store;
			return $store->count($store->find($this->path, $this->query, $this->limit, $this->offset));
		}

		public function limit($limit) {
			$clone = clone $this;
			$clone->limit = $limit;
			return $clone;
		}

		public function offset($offset) {
			$clone = clone $this;
			$clone->offset = $offset;
			return $clone;
		}

		public function order($order) {
			$clone = clone $this;
			$clone->order = $order;
			return $clone;
		}

	}

	class ar_storeList extends ar_storeFind {

		public function __construct($path) {
			parent::__construct($path, "object.parent = '".$path."'");
		}

	}

	class ar_storeGet extends arBase {

		public function __construct($path) {
			global $store;
			$this->path = $path;
		}

		public function find( $query = "" ) {
			return new ar_storeFind($this->path, $query);
		}

		public function ls() {
			return new ar_storeList($this->path);
		}

		public function call($template, $args=null) {
			global $store;
			if ($template instanceof ar_listExpression_Pattern ) {
				$template = ar::listExpression( 1 )->pattern( $template );
			}
			if (ar_store::$rememberShortcuts) {
				$path = ar_store::makeRealPath( $this->path );
			} else {
				$path = $this->path;
			}
			return $store->call($template, $args, $store->get($path), array( 'usePathAsKey' => true ) );
		}

		public function parents() {
			return new ar_storeParents($this->path);
		}

	}

	class ar_storeParents extends arBase {

		public function __construct($path = ".") {
			$this->path	= $path;
			$this->top	= "/";
		}

		public function call($template, $args=null) {
			global $store;
			if ($template instanceof ar_listExpression_Pattern ) {
				$template = ar::listExpression( $this->count() )->pattern( $template );
			}
			if (ar_store::$rememberShortcuts) {
				$path     = ar_store::makePath($this->path);
				$realpath = ar_store::makeRealPath($this->path);
				if ($realpath != $path ) {
					// must do a call for each seperate path.
					$list   = array();
					$parent = $path;
					while ( $realpath != $this->top && $parent != $this->top && end($list) != $realpath ) {
						$list[$parent]   = $realpath;
						$parent   = ar_store::makePath($parent.'../');
						$realpath = ar_store::makeRealPath($parent);
					}
					if ( ($realpath == $this->top) || ($parent == $this->top) ) {
						$list[$parent] = $realpath;
					}
					$list = array_reverse($list);
					$result = array();
					foreach ($list as $virtualpath => $path ) {
						$result[$virtualpath] = current($store->call($template, $args, $store->get($path), array( 'usePathAsKey' => true ) ) );
					}
					return $result;
				}
			}
			return $store->call($template, $args, $store->parents($this->path, $this->top), array( 'usePathAsKey' => true ) );
		}

		public function count() {
			global $store;
			return $store->count($store->parents($this->path, $this->top));
		}

		public function top($top = "/") {
			$clone = clone $this;
			$clone->top = $top;
			return $clone;
		}

	}


?>