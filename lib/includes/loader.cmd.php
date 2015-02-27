<?php
    /******************************************************************
     loader.cmd.php                                        Muze Ariadne
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

	Contains all loader functions for the commandline interface
	to Ariadne.

    ******************************************************************/

	// compatibility with loaders in the bin directory which where not yet updated
	if( ! defined('AriadneBasePath') ){
		global $ariadne;
		$ARLoader = 'cmd';
		trigger_error('bootstrap.php not included, please update your loader',E_USER_DEPRECATED);
		require_once($ariadne."/bootstrap.php");
	}

	$DB["method"]["loader"] = false;
	$DB["method"]["file"] = true;
	$DB["file"] = "php://stderr";

	function error($text) {
		debug("Error: $text");
	}

	function ldRegisterFile($field = "file", &$error) {
	global $ARnls, $store, $ldCmd_files;
		debug("ldRegisterFile([$field], [error])");

		require_once($store->get_config("code")."modules/mod_mimemagic.php");

		$result = Array();
		$file_data = $ldCmd_files[$field];
		if ($file_data) {
				$file_temp = tempnam($store->get_config("files")."temp", "upload");
				$fp = fopen($file_temp, "wb+");
				if (!$fp) {
					$error = "could not write file '$field'";
				} else {
					debug("	file_data (".$file_data.")");
					fwrite($fp, $file_data, strlen($file_data));
					fclose($fp);

					$file_type = get_mime_type($file_temp);

					$result[$field] = $field;
					$result[$field."_temp"] = basename($file_temp);
					$result[$field."_size"] = filesize($file_temp);
					$result[$field."_type"] = $file_type;
					debug(" http_post_vars (".serialize($result).")");
				}
		}
		debug("ldRegisterFile[end] ($result)");
		return $result;
	}

	function ldAccessDenied($path, $message) {
	global $ARCurrent;
		if (!$ARCurrent->arLoginSilent) {
			echo "\n($path) $message\n";
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

	function ldGetRequestedHost() {
		// dummy function
	}

	function ldGetUserCookie($cookiename="ARUserCookie") {
		$cookie = false;
		return $cookie;
	}

	function ldSetUserCookie($cookie, $cookiename="ARUserCookie", $expire=null, $path="/", $domain="", $secure=0) {
		$result = false;
		return $result;
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

	function ldGetServerVar($server_var = "") {
		// dummy function
		return false;
	}

	function ldGetClientVar($client_var) {
		// dummy function
		return false;
	}

	function ldObjectNotFound($requestedpath, $requestedtemplate) {
		echo "NOT FOUND: [".$requestedpath."][".$requestedtemplate."]";
	}

	function ldParseOption($cmd) {
		$result = false;
		if (strpos($cmd, "--") === 0) {
			$result = array ('cmd' => $cmd);
			$option_switch=substr($cmd, 2);

			if (strpos($option_switch,'=') !== false) {
				$result['value'] = substr($option_switch, strpos($option_switch, "=")+1);
				$option_switch=substr($option_switch,0,strpos($option_switch, "="));
			}

			if (strpos($option_switch,"-")!==false) {
				$result['subswitch'] = substr($option_switch, strpos($option_switch,"-")+1);
				$option_switch=substr($option_switch, 0, strpos($option_switch,"-"));
			}
			$result['switch'] = $option_switch;
		}
		return $result;
	}

	function ldDisablePostProcessing() {
		// dummy function
		return false;
	}
