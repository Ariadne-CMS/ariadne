<?php

	class ar {

		function ls() {
			$context = pobject::getContext();
            $me = $context["arCurrentObject"];
			return new arList($me->path);
		}

		function find($query="") {
			$context = pobject::getContext();
            $me = $context["arCurrentObject"];
			return new arFind($me->path, $query);
		}

		function get($path="") {
			$context = pobject::getContext();
            $me = $context["arCurrentObject"];
			return new arGet($me->path, $path);			
		}

		function parents() {
			$context = pobject::getContext();
            $me = $context["arCurrentObject"];
			return new arParents($me->path);
		}

	}


	class pinp_ar {

		function _ls() {
			return ar::ls();
		}

		function _find($query="") {
			return ar::find($query);
		}

		function _get($path="") {
			return ar::get($path);
		}

		function _parents() {
			return ar::parents();
		}

	}


	class arFind {

		var $limit = 100;
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

		function count() {
			global $store;
			return $store->count($store->find($this->path, $this->query, $this->limit, $this->ofset));
		}

		function limit($limit) {
			$this->limit = $limit;
			return $this;
		}

		function offset($offset) {
			$this->offset = $offset;
			return $this;
		}
	
		function order($order) {
			$this->order = $order;
			return $this;
		}

		function _call($template, $args=null) {
			return $this->call($template, $args);
		}

		function _count() {
			return $this->count();
		}

		function _limit($limit) {
			return $this->limit($limit);
		}

		function _offset($offset) {
			return $this->offset($offset);
		}

		function _order($order) {
			return $this->order($order);
		}

	}

	class arList extends arFind {

		function __construct($path) {
			global $store;
			$parent = $store->make_path($path, '../');
			parent::__construct($path, "object.parent = '".$parent."'");
		}

	}


	class arGet {
		
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

		function _find($query) {
			return $this->find($query);
		}

		function _call($template, $args=null) {
			return $this->call($template, $args);
		}

	}

	class arParents {

		function __construct($path) {
			$this->path = $path;
		}

		function call($template, $args=null) {
			global $store;
			return $store->call($template, $args, $store->parents($this->path));
		}

		function count() {
			global $store;
			return $store->count($store->parents($this->path));
		}

	}


?>