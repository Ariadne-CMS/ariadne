<?php

ar_pinp::allow('ar_connect_db');
ar_pinp::allow('ar_connect_dbClient');

class ar_connect_db extends arBase {
	
	function connect( $dsn, $username='', $password='', $driver_options=array() ) {
		// deprecated
		return self::client( $dsn, $username, $password, $driver_options );
	}
	
	function client( $dsn, $username='', $password='', $driverOptions = array() ) {
		try {
			return new ar_connect_dbClient( new PDO($dsn, $username, $password, $driverOptions) );
		} catch( Exception $e ) {
			return ar::error( $e->getMessage(), $e->getCode() );
		}
	}

}

// FIXME: define an interface

class ar_connect_dbClient extends arWrapper implements Iterator {
	
	function __construct( $wrapped ) {
		parent::__construct($wrapped);
		$this->__class = 'ar_connect_dbClient';
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
		// note: somehow isset() returns false here, so use is_array instead.
		return is_array($this->row);
	}

}
?>