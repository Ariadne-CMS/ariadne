<?php

class pinp_error {

	public static function _raiseError($message, $error ) {
		return ar_error::raiseError($message, $error);
	}

	public static function _isError($ob) {
		return ar_error::isError($ob);
	}
}
