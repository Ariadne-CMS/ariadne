<?php
    /******************************************************************
     loader.php                                            Muze Ariadne
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

	Loader for the Ariadne WebDAV Interface.

    ******************************************************************/

	require_once("./ariadne.inc");
	require_once($ariadne."/configs/ariadne.phtml");
	require_once($ariadne."/configs/webdav/default.phtml");
	require_once($ariadne."/configs/authentication.phtml");
	require_once($ariadne."/configs/store.phtml");
	include_once($store_config['code']."stores/".$store_config["dbms"]."store.phtml");
	include_once($store_config['code']."modules/mod_session.phtml");
	include_once($store_config['code']."includes/loader.webdav.php");
	include_once($store_config['code']."modules/mod_auth/".$auth_config['method'].".php");
	include_once($store_config['code']."modules/mod_webdav.php");
	include_once($store_config['code']."modules/mod_ar.php");

	function fix_quotes(&$value) {
		if (is_array($value)) {
			reset($value);
			array_walk($value, 'fix_quotes');
		} else {
			$value=stripslashes($value);
		}
	}

	$AR_PATH_INFO=$_SERVER["PATH_INFO"];
	if (!$AR_PATH_INFO) {
		$AR_PATH_INFO = '/';
	}


	// needed for IIS: it doesn't set the PHP_SELF variable.
	if(!isset($_SERVER["PHP_SELF"])) {
		$_SERVER["PHP_SELF"]=$_SERVER["SCRIPT_NAME"].$AR_PATH_INFO;
	}
	if (Headers_sent()) {
		error("The loader has detected that PHP has already sent the HTTP Headers. This error is usually caused by trailing white space or newlines in the configuration files. See the following error message for the exact file that is causing this:");
		Header("Misc: this is a test header");
	}
	@ob_end_clean(); // just in case the output buffering is set on in php.ini, disable it here, as Ariadne's cache system gets confused otherwise. 

	$root=$AR->root;
	$session_id=0;

	$ARCookie=stripslashes($_COOKIE["ARCookie"]);
	$cookie=@unserialize($ARCookie);
	if (is_array($cookie)) {
		$session_id=current(array_keys($cookie));
	}

	// find (and fix) arguments
	set_magic_quotes_runtime(0);
	if (get_magic_quotes_gpc()) {
		// this fixes magic_quoted input
		fix_quotes($_GET);
		fix_quotes($_POST);
		$ARCookie=stripslashes($ARCookie);
	}
	$args=array_merge($_GET, $_POST);


	$nls=$AR->nls->default;

	// instantiate the store
	$inst_store = $store_config["dbms"]."store";
	$store=new $inst_store($root,$store_config);
	$store->rootoptions = $rootoptions;

	if ($session_id) {
		//debugon("all");
		debug("webdav:loader starting session $session_id");
		ldStartSession($session_id);

		if ($ARCurrent->session->get("ARSessionTimedout", 1)) {
			if (!$ARCurrent->session->get("oldArCallArgs", 1)) {
				$ARCurrent->session->put("oldArCallArgs", $args, 1);
				$ARCurrent->session->save(0, true);
			}
		} else {
			if ($ARCurrent->session->get("oldArCallArgs", 1)) {
				$args = $ARCurrent->session->get("oldArCallArgs", 1);
				$ARCurrent->session->put("oldArCallArgs", "", 1);
			}
		}
	}


	// load language file
	require($ariadne."/nls/$nls");
	// system template: no language check
	$ARCurrent->nolangcheck=1;

	register_shutdown_function("ldOnFinish");

	$webdavserver = new Ariadne_WebDAV_Server($store,$webdav_config);
	$webdavserver->ServeRequest();
	
	/* Finish execution */
	exit;
?>