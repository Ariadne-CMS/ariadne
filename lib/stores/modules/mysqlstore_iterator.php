<?php

class mysqlstoreIterator implements Iterator {
	protected $definitions;
	protected $store;
	protected $objects;
	protected $selectorIterator;
	protected $currentObj;
	protected $position;
	protected $count;

	function __construct($selector, $definitions, $store, $objects) {
		$this->definitions	= $definitions;
		$this->store		= $store;
		$this->objects		= $objects;
		$this->count		= @mysql_num_rows($objects["list"]);
		$this->position		= 0;
		$this->index		= 0;
		$this->selectorIterator	= $selector->getIterator($this->count, $definitions);
		$this->currentObj	= false;
	}

	function rewind() {
		$this->selectorIterator->rewind();
		$this->position = 0;
		$this->index = 0;
		while ($this->selectorIterator->current() === "NULL") {
			$this->position++;
			$this->selectorIterator->next();
		}
		@mysql_data_seek($this->objects["list"], $this->position);
		$this->currentObj = false;
	}

	function current() {
		$item = $this->selectorIterator->current();
		if (!$this->currentObj) {
			$row = @mysql_fetch_array($this->objects["list"]);
			$this->currentObj = $this->store->newobject($row["path"], $row["parent"], $row["type"], unserialize($row["object"]), $row["id"], $row["lastchanged"], $row["vtype"], strlen($row["object"]), $row["priority"]);
		}
		if (is_array($item)) {
			$template	= array_shift($item);
			$args		= $item;
		} else {
			$template	= $item;
		}
		return $this->currentObj->call($template, $args);
	}

	function key() {
		return $this->index;
	}

	function next() {
		$this->selectorIterator->next();
		while ($this->selectorIterator->current() === "NULL") {
			$this->position++;
			$this->selectorIterator->next();
		}
		$this->index++;
		@mysql_data_seek($this->objects["list"], ++$this->position);
		$this->currentObj = false;
	}

	function valid() {
		return ($this->position >= 0 && $this->position < $this->count);
	}

}
