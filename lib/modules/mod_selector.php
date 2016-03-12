<?php
	require_once("mod_selector/nodes.php");
	require_once("mod_selector/parser.php");

	class selector {
		protected $nodes;

		public function __construct($string) {
			$parser = new selectorParser($string);
			$this->nodes = $parser->parse();
		}

		public function run($count, $offset = 0, $definitions = Array()) {
			if (!$this->nodes) {
				return false;
			}
			if ($offset >= $count) {
				return false;
			}

			$result = $this->nodes->run($count, $offset);
			if (isset($definitions[$result])) {
				$result = $definitions[$result];
			}

			return $result;
		}

		public function getIterator($count, $definitions = Array()) {
			return new selectorIterator($this, $count, $definitions);
		}

	}

	class selectorIterator implements Iterator {

		public function __construct($selector, $count, $definitions = Array()) {
			$this->selector		= $selector;
			$this->count		= $count;
			$this->offset		= 0;
			$this->definitions	= $definitions;
		}

		public function current() {
			return $this->selector->run($this->count, $this->offset, $this->definitions);
		}

		public function key() {
			return $this->offset;
		}

		public function next() {
			$this->offset = $this->offset + 1;
		}

		public function rewind() {
			$this->offset = 0;
		}

		public function valid() {
			return ($this->offset >= 0);
		}

	}

	class pinp_selector extends selector {

		public function _create($string) {
			return new pinp_selector($string);
		}

		public function _run($count, $offset = 0, $definitions = Array()) {
			return $this->run($count, $offset, $definitions);
		}

		public function _getIterator($count, $definitions = Array()) {
			return new pinp_selectorIterator($count, $definitions);
		}

	}


	class pinp_selectorIterator extends selectorIterator {

		public function __construct($selector, $count, $definitions = Array()) {
			return parent::__construct($selector, $count, $definitions);
		}

		public function _current() {
			return $this->current();
		}

		public function _key() {
			return $this->key();
		}

		public function _next() {
			return $this->next();
		}

		public function _rewind() {
			return $this->rewind();
		}

		public function _valid() {
			return $this->valid();
		}

	}
