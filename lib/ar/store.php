<?php
	
	require_once(dirname(__FILE__).'/../ar.php');

	ar_pinp::allow('ar_store');

	ar_pinp::allow('ar_storeFind'); 

	ar_pinp::allow('ar_storeGet');

	ar_pinp::allow('ar_storeParents');

	class ar_store extends arBase {
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
			return new ar_storeGet($me->path, $path);
		}

		public static function parents() {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return new ar_storeParents($me->path);
		}
		
		public static function exists($path = ".") {
			global $store;
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return $store->exists($store->make_path($me->path, $path));
		}

		public static function currentSite() {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return $me->currentsite();
		}
		
		public static function parentSite( $site ) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return $me->parentsite( $site );		
		}
		
		public static function currentSection() {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return $me->currentsection();		
		}

		public static function parentSection( $section ) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return $me->parentsection( $section );		
		}

		public static function makePath( $path = '' ) {
			global $store;
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return $store->make_path($me->path, $path);
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
			$result = $store->call($template, $args, $store->find($this->path, $this->query, $this->limit, $this->offset), array( 'usePathAsKey' => true ) );
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

		public function __construct($current, $path) {
			global $store;
			$this->path = $store->make_path($current, $path);
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
			return $store->call($template, $args, $store->get($this->path), array( 'usePathAsKey' => true ) );
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