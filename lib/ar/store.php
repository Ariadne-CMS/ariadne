<?php

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

		public static function parents($path = ".") {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return new ar_storeParents($me->make_path($path));
		}

	}

	class ar_storeFind extends arBase {

		var $limit = 0;
		var $offset = 0;
		var $order = '';
		var $query = '';
		var $path = '/';

		function __construct($path='/', $query='') {
			$this->path = $path;
			$this->query = $query;
		}

		function call($template, $args=null) {
			global $store;
			return $store->call($template, $args, $store->find($this->path, $this->query, $this->limit, $this->offset)); 
		}

		function iterate($selection, $definitions = Array()) {
			global $store;
			$result = Array();
			$iterator = $this->getIterator($selection, $definitions, $store->find($this->path, $this->query, $this->limit, $this->offset)); 
			foreach ($iterator as $key => $value) {
				$result[$key] = $value;
			}
			return $result;
		}

		function getIterator($selection, $definitions = Array()) {
			global $store;
			return $store->getIterator(new selector($selection), $definitions, $store->find($this->path, $this->query, $this->limit, $this->offset));
		}

		function count() {
			global $store;
			return $store->count($store->find($this->path, $this->query, $this->limit, $this->ofset));
		}

		function limit($limit) {
			$clone = clone $this;
			$clone->limit = $limit;
			return $clone;
		}

		function offset($offset) {
			$clone = clone $this;
			$clone->offset = $offset;
			return $clone;
		}

		function order($order) {
			$clone = clone $this;
			$clone->order = $order;
			return $clone;
		}

	}

	class ar_storeList extends ar_storeFind {

		function __construct($path) {
			parent::__construct($path, "object.parent = '".$path."'");
		}

	}


	class ar_storeGet extends arBase {

		function __construct($current, $path) {
			global $store;
			$this->path = $store->make_path($current, $path);
		}

		function find($query) {
			return new arFind($this->path, $query);
		}

		function ls() {
			return new arList($this->path);
		}

		function call($template, $args=null) {
			global $store;
			return $store->call($template, $args, $store->get($this->path));
		}

	}

	class ar_storeParents extends arBase {

		function __construct($path = ".") {
			$this->path	= $path;
			$this->top	= "/";
		}

		function call($template, $args=null) {
			global $store;
			return $store->call($template, $args, $store->parents($this->path, $this->top));
		}

		function iterate($selection, $definitions = Array()) {
			global $store;
			$result = Array();
			$iterator = $this->getIterator($selection, $definitions, $store->parents($this->path, $this->top)); 
			foreach ($iterator as $key => $value) {
				$result[$key] = $value;
			}
			return $result;
		}

		function getIterator($selection, $definitions = Array()) {
			global $store;
			return $store->getIterator(new selector($selection), $definitions, $store->parents($this->path, $this->top));
		}

		function count() {
			global $store;
			return $store->count($store->parents($this->path, $this->top));
		}

		function top($top = "/") {
			$clone = clone $this;
			$clone->top = $top;
			return $clone;
		}

	}


?>
