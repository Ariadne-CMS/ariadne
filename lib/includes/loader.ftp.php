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

	include_once($store_config['code']."modules/mod_debug.php");
	
	$DB["method"]["loader"] = false;
	$DB["method"]["file"] = true;
	$DB["method"]["syslog"] = true;
	$DB["file"] = $ftp_config["debugfile"];

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

	function ldDisablePostProcessing() {
		// dummy function
		return false;
	}

?>