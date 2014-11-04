<?php
	require_once($AR->dir->install.'/lib/ar.php');
/*

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

		function parents($path = ".") {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			return new arParents($me->make_path($path));
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

		function _call($template, $args=null) {
			return $this->call($template, $args);
		}

		function _iterate($selection, $definitions = Array()) {
			return $this->iterate($selection, $definitions);
		}

		function _getIterator($selection, $definitions = Array()) {
			return $this->getIterator($selection, $definitions);
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
			parent::__construct($path, "object.parent = '".$path."'");
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

		function _ls() {
			return $this->ls();
		}

		function _call($template, $args=null) {
			return $this->call($template, $args);
		}

	}

	class arParents {

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

		function _call($template, $args=null) {
			return $this->call($template, $args);
		}

		function _iterate($selection, $definitions = Array()) {
			return $this->iterate($selection, $definitions);
		}

		function _count() {
			return $this->count();
		}

		function _top($top = "/") {
			return $this->top($top);
		}

	}

*/
