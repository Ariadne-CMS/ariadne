<?php
    /******************************************************************
     loader.ftp.php                                        Muze Ariadne
     ------------------------------------------------------------------
     Author: Muze (info@muze.nl)
     Date: 11 december 2002

     Copyright 2002 Muze

     This file is part of Ariadne.

     Ariadne is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published 
     by the Free Software Foundation; either version 2 of the License, 
     or (at your option) any later version.
 
     Ariadne is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with Ariadne; if not, write to the Free Software 
     Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  
     02111-1307  USA

    -------------------------------------------------------------------

     Description:

	Contains all loader functions for the Ariadne FTP interface.

    ******************************************************************/

	// debugging functions.

	$DB["all"]=5;
	$DB["store"]=4;
	$DB["class"]=3;
	$DB["object"]=2;
	$DB["pinp"]=1;
	$DB["off"]=0;
	$DB["level"]=$DB["off"];
	$DB["stream"]="all";

	$DB["file"]=$ftp_config["debugfile"];

	function debug($text, $level="pinp", $stream="all", $indent="") {
	global $DB, $DB_INDENT, $AR;
		if ( ($DB["level"]>=$DB[$level]) && (($DB["stream"]=="all") || ($DB["stream"]==$stream))) {
			if ($indent=="OUT") {
				$DB_INDENT=substr($DB_INDENT,0,-2);
			}
			if ( ($AR->DEBUG == 'SYSLOG') || ($AR->DEBUG == 'BOTH') ) {
				syslog(LOG_NOTICE,"(Ariadne) $level::$stream::$text");
			}

			fwrite($DB["fp"], "$DB_INDENT $level::$stream::$text\n");
			fflush($DB["fp"]);

			flush();
			if ($indent=="IN") {
				$DB_INDENT.="  ";
			}
		}
	}

	function debugon($level="pinp", $stream="all") {
	global $DB;
		$DB["fp"]=fopen($DB["file"], "a+");
		if ($DB["fp"]) {
			$DB["level"]=$DB[$level];
			$DB["stream"]=$stream;
			debug("Debuglevel: $level Stream: $stream");
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

	function ldGetCredentials() {
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

	function ldGetServerVar($server_var) {
		// dummy function
		return false;
	}

	function ldGetClientVar($client_var) {
		// dummy function
		return false;
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