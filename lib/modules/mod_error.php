<?php

class error {
	var $message;
	var $code;

	function __construct($message, $code) {
		$this->message=$message;
		$this->code=$code;
	}

	function isError($ob) {
		return (is_a($ob, 'error') || is_a($ob, 'PEAR_Error'));
	}

	function raiseError($message, $code) {
		return new error($message, $code);
	}

	function getMessage() {
		return $this->message;
	}

	function getCode() {
		return $this->code;
	}

	function _raiseError($message, $error) {
		return error::raiseError($message, $error);
	}

	function _isError($ob) {
		return error::isError($ob);
	}

	function _getMessage() {
		return $this->getMessage();
	}

	function _getCode() {
		return $this->getCode();
	}

}

class pinp_error extends error {

	function pinp_error($message, $code) {
		$this->error($message, $code);
	}

}

	
?>