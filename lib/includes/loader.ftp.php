<?php
	// debugging functions.

	$DB["all"]=5;
	$DB["store"]=4;
	$DB["class"]=3;
	$DB["object"]=2;
	$DB["pinp"]=1;
	$DB["off"]=0;
	$DB["level"]=$DB["off"];

	$DB["file"]=$ftp_config["debugfile"];

	function debug($text, $level="pinp", $indent="") {
		global $DB, $DB_INDENT;
	 	if ($DB["level"]>=$DB[$level]) {
			if ($indent=="OUT") {
				$DB_INDENT=substr($DB_INDENT,0,-2);
			}
			fwrite($DB["fp"], "$DB_INDENT $level::$text\n");
			fflush($DB["fp"]);
			if ($indent=="IN") {
				$DB_INDENT.="  ";
			}
		}
	}

	function debugon($level="pinp") {
		global $DB;
		if (file_exists($DB["file"])) {
			$DB["fp"]=fopen($DB["file"], "a+");
			if ($DB["fp"]) {
				$DB["level"]=$DB[$level];
				debug("Debuglevel: $level");
			}
		}
	}

	function debugoff() {
		global $DB;
		if ($DB["fp"]) {
			debug("Debugging off.");
			$DB["level"]=$DB["off"];
			@fclose($DB["fp"]);
		}
	}

	function error($text) {
		debug("Error: $text");
	}


	function ldAccessDenied($path, $message) {
	global $ARCurrent;
		if (!$ARCurrent->arLoginSilent) {
			$ARCurrent->ftp_error = "($path) $message";
		}
	}

	function ldSetRoot($session='', $nls='') {
		// dummy function
	}

	function ldSetNls($nls) {
		// dummy function
	}

	function ldSetSession($session='') {
		// dummy function
	}
 
	function ldStartSession($sessionid='') {
		// dummy function
	}

	function ldSetCache($file, $time, $image, $headers) {
		// dummy function
	}

	function ldSetCredentials($login, $password) {
	}

	function ldCheckCredentials($login, $password) {
	}

	function ldRedirect($uri) {
	}

	function ldHeader($header) {
	}

	function ldSetClientCache($cache_on, $expires=0, $modified=0) {
		return true;
	}

	function ldSetContent($mimetype, $size=0) {
		return true;
	}


	/*	PHP-4.0.6 compat functions	*/

	if (!function_exists("socket_create")) {
		debug("running older php version: creating socket alias functions");

		function socket_create($arg1, $arg2, $arg3) {
			return socket($arg1, $arg2, $arg3);
		}

		function socket_connect($arg1, $arg2, $arg3) {
			return connect($arg1, $arg2, $arg3);
		}

		function socket_accept($arg1) {
			return accept_connect($arg1);
		}

		function socket_close($arg1) {
			return close($arg1);
		}

		function socket_bind($arg1, $arg2, $arg3) {
			return !bind($arg1, $arg2, $arg3);
		}
		
		function socket_listen($arg1, $arg2) {
			return listen($arg1, $arg2);
		}

		function socket_write($arg1, $arg2, $arg3) {
			return write($arg1, $arg2, $arg3);
		}

		function socket_read($arg1, $arg2, $arg3) {
			$buffer="";
			read($arg1, $buffer, $arg2, PHP_BINARY_READ);
			return $buffer;
		}
	}	

?>