<?php
	require_once("mod_selector/nodes.php");
	require_once("mod_selector/parser.php");

	class selector {

		function selector($string) {
			$parser = new selectorParser($string);
			$this->nodes = $parser->parse();
		}

		function run($count, $offset = 0, $definitions = Array()) {
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

		function &getIterator($count, $definitions = Array()) {
			return new selectorIterator($this, $count, $definitions);
		}

	}

	class selectorIterator implements Iterator {

		function __construct($selector, $count, $definitions = Array()) {
			$this->selector		= $selector;
			$this->count		= $count;
			$this->offset		= 0;
			$this->definitions	= $definitions;
		}

		function current() {
			return $this->selector->run($this->count, $this->offset, $this->definitions);
		}

		function key() {
			return $this->offset;
		}

		function next() {
			$this->offset = $this->offset + 1;
		}

		function rewind() {
			$this->offset = 0;
		}

		function valid() {
			return ($this->offset >= 0);
		}

	}

	class pinp_selector extends selector {

		function _create($string) {
			return new pinp_selector($string);
		}

		function _run($count, $offset = 0, $definitions = Array()) {
			return $this->run($count, $offset, $definitions);
		}

		function &_getIterator($count, $definitions = Array()) {
			return new pinp_selectorIterator($count, $definitions);
		}

	}


	class pinp_selectorIterator extends selectorIterator {

		function pinp_selectorIterator($selector, $count, $definitions = Array()) {
			return selectorIterator::selectorIterator($selector, $count, $definitions);
		}

		function _current() {
			return $this->current();
		}

		function _key() {
			return $this->key();
		}

		function _next() {
			return $this->next();
		}

		function _rewind() {
			return $this->rewind();
		}

		function _valid() {
			return $this->valid();
		}

	}

?>