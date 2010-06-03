<?php

ar_pinp::allow('ar_connect_db');
ar_pinp::allow('ar_connect_dbWrapper');

class ar_connect_db extends arBase {
	
	function connect( $dsn, $username='', $password='', $driver_options=array() ) {
		try {
			return new ar_connect_dbWrapper( new PDO($dsn, $username, $password, $driver_options) );
		} catch( Exception $e ) {
			return ar::error( $e->getMessage(), $e->getCode() );
		}
	}

}

class ar_connect_dbWrapper extends arWrapper implements Iterator {
	
	function __construct( $wrapped ) {
		parent::__construct($wrapped);
		$this->__class = 'ar_connect_dbWrapper';
		if ($this->wrapped instanceof PDOStatement) {
			$this->row = $this->wrapped->fetch();
			if (!$this->row) {
				$this->row = null;
			}
		}
		$this->cursor = 0;
	}
	
	function current() {
		return $this->row;
	}
	
	function key() {
		return $this->cursor;
	}
	
	function next() {
		if ($this->wrapped instanceof PDOStatement) {
			$this->row = $this->wrapped->fetch();
		}
		if (!$this->row) {
			$this->row = null;
		}
		$this->cursor++;
	}
	
	function rewind() {
		if ($this->wrapped instanceof PDOStatement) {
			$this->wrapped->execute();
			$this->row = $this->wrapped->fetch();
		}
		if (!$this->row) {
			$this->row = null;
		}
		$this->cursor = 0;
	}
	
	function valid() {
		return isset($this->row);
	}

}
?>