<?php
    /******************************************************************
     loader.webdav.php                                     Muze Ariadne
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

       This is loader that contains all functions for the Ariadne web
       interface.

    ******************************************************************/

	$ERRMODE="htmljs"; // alternative: "text"/"html"/"js"

	define('LD_ERR_ACCESS', -1);
	define('LD_ERR_SESSION', -2);

	include_once($store_config['code']."modules/mod_debug.php");
	include_once($store_config['code']."includes/loader.webdav.auth.php");

	$DB["method"]["loader"] = false;
	$DB["method"]["file"] = true;

	if($webdav_config['debugfile']) {
		$DB["file"] = $webdav_config['debugfile'];
	} else {
		$DB["file"] = '/tmp/webdav.log';
	}

	function debug_print( $text ) {
		echo "<b>".$text."</b><br>";
		flush();
	}

	function error($text) {
		global $ERRMODE;
		switch ($ERRMODE) {
			case "html" :
				echo "<b><font color='red'>Error: $text</font></b><BR>\n";
				break;
			case "js" :
				echo "\nalert('Error: $text');\n";
				break;
			case "text" :
				echo "\nERROR: $text\n";
				break;
			case "htmljs" :
			default: 
				echo "// <b><font color='red'>Error: $text</font></b><BR>\n<!--\nalert('Error: $text');\n// -->\n";
				break;
		}
	}

	function ldRegisterFile($field = "file", &$error) {
	global $ARnls, $store;

		require_once($store->code."modules/mod_mimemagic.php");

		$result = Array();

		$file_temp=$_FILES[$field]['tmp_name'];
		$file=$_FILES[$field]['name'];
		if ($file && is_uploaded_file($file_temp)) {
			// new file uploaded -> save it before PHP deletes it
			$file_artemp=tempnam($store->get_config("files")."temp","upload");
			if (move_uploaded_file($file_temp, $file_artemp)) {
				// now make the new values available to wgWizKeepVars()
				$result[$field]=$file;
				$result[$field."_temp"]=substr($file_artemp,strlen($store->get_config("files")."temp"));
				$result[$field."_size"]=(int)$_FILES[$field]['size'];
				$type = get_mime_type($file_artemp);
				if (!$type) {
					$type = get_mime_type($file, MIME_EXT);
				}
				$result[$field."_type"]=$type;
			}
		}
		return $result;
	}

	function ldOnFinish() {
	global $ARCurrent, $store;

		if ($ARCurrent->session) {
			$ARCurrent->session->save();
		}
		if ($store) {
			$store->close();
		}
	}

	function ldObjectNotFound($requestedpath, $requestedtemplate) {
	global $store, $AR;

		$path=$requestedpath;
		if (!$path) {
			error("Empty path requested with template: $requestedtemplate");
		} else {
			while ($path!=$prevPath && !$store->exists($path)) {
				$prevPath=$path;
				$path=$store->make_path($path, "..");
			}
			if ($prevPath==$path) {
				error("Database is not initialised, please run <a href=\"".$AR->dir->www."install/install.php\">the installer</a>");
			} else {
				// no results: page couldn't be found, show user definable 404 message
				$store->call("user.notfound.html",
					 Array(	"arRequestedPath" => $requestedpath,
					 		"arRequestedTemplate" => $requestedtemplate ),
					 $store->get($path));
			}
		}
	}


	function ldAccessDenied($path, $message) {
	global $ARCurrent, $store;
		/* 
			since there is no 'peek' function, we need to pop and push
			the arCallArgs variable.
		*/

		$arCallArgs = array_pop($ARCurrent->arCallStack);
		array_push($ARCurrent->arCallStack, $arCallArgs);

		if (!$arCallArgs || is_array($arCallArgs)) {
			$arCallArgs["arLoginMessage"] = $message;
		} else {
			$arCallArgs.="&arLoginMessage=".urlencode($message);
		}
		if (!$ARCurrent->arLoginSilent) {
			$ARCurrent->arLoginSilent = true;
			$store->call("user.login.html", 
								$arCallArgs,
								$store->get($path) );
		}

	}

	function ldSetRoot($session='', $nls='') {
	global $store, $AR, $ARCurrent, $root, $rootoptions;

		$root=$AR->root;
		$rootoptions="";
		if ($nls) {
			$rootoptions.="/$nls";
			$ARCurrent->nls=$nls;
		}
		$root.=$rootoptions;
		if ($store) { // loader.php uses this function before the store is initialized.
			$store->root=$root;
			$store->rootoptions=$rootoptions;
		}	
	}

	function ldSetNls($nls) {
	global $ARCurrent;

		$session=$ARCurrent->session->id;
		ldSetRoot($session, $nls);
	}

	function ldSetSession($session='') {
	global $ARCookie, $AR, $ARCurrent;

		$nls=$ARCurrent->nls;
		$check = ldGetCredentials();
		if (!$check[$ARCurrent->session->id]) {
			$cookie = Array();
			$cookie[$ARCurrent->session->id]['timestamp']=time();
			$ARCookie=json_encode($cookie);
			debug("setting cookie ($ARCookie)");
			setcookie("ARCookie",$ARCookie, 0, '/');
		}
		ldSetRoot($session, $nls);
	}
 
	function ldStartSession($sessionid='') {
	global $ARCurrent, $AR, $ariadne, $LD_NO_SESSION_SUPPORT;
		if ($LD_NO_SESSION_SUPPORT) {
			debug("ldStartSession($sessionid): no session support");
			return;
		}

		require($ariadne."/configs/sessions.phtml");
		$ARCurrent->session=new session($session_config,$sessionid);
		ldSetSession($ARCurrent->session->id);
	}

	function ldSetCache($file, $time, $image, $headers) {
	global $store;

		debug("ldSetCache($file, $time, [image], [headers])","object");
		if ($time==-2) {
			$time=0;
		} else {
			$time=time()+($time*3600);
		}
		if (!preg_match("/\.\./",$file)) {
			if ($image) {
				$path=substr($file, 1, strrpos($file, "/")-1);
				if (!file_exists($store->get_config("files")."cache/".$path)) {
					ldMkDir("cache/".$path);
					ldMkDir("cacheheaders/".$path);
				}
				$fp=fopen($store->get_config("files")."cache".$file, "wb");
				fwrite($fp, $image);
				fclose($fp);
				$fp=fopen($store->get_config("files")."cacheheaders".$file, "wb");
				fwrite($fp, $headers);
				fclose($fp);
				if (!touch($store->get_config("files")."cache".$file, $time)) {
					debug("ldSetCache: ERROR: couldn't touch image","object");
				}
			}
		}
	}

	function ldMkDir($dir) {
	global $store;

		debug("ldMkDir($dir)","object");
		$dir=strtok($dir, "/");
		$curr=$store->get_config("files");
		while ($dir) {
			$curr.=$dir."/";
			debug("ldMkDir: $curr","all");
			@mkdir($curr, 0755);
			$dir=strtok("/");
		}
	}

	function ldGetUserCookie($cookiename="ARUserCookie") {
	
		$cookie = false;
	
		if( $_COOKIE[$cookiename] && !($cookiename == "ARCookie")) {

			debug("ldGetUserCookie() = $ARUserCookie","object");
			$cookie=json_decode($ARCookie,true);
			if ($cookie === null) {
				$cookie=unserialize($ARCookie);
			}
		}
		return $cookie;
	}

	function ldSetUserCookie($cookie, $cookiename="ARUserCookie", $expire=null, $path="/", $domain="", $secure=0) {

		$result = false;

		if( $cookiename != "ARCookie") {
			$ARUserCookie=json_encode($cookie);
			debug("ldSetUserCookie(".$ARUserCookie.")","object");
			$result = setcookie($cookiename,$ARUserCookie, $expire, $path, $domain, $secure);
		}

		return $result;
	}

	function ldRedirect($uri) {
		debug("ldRedirect($uri)");
		return ldHeader("Location: $uri");
	}

	function ldHeader($header) {
	global $ARCurrent;

		$result=false;
		if (!Headers_sent()) {
			$result=true;
			if (is_array($header)) {
				$header=implode('\n',$header);
			}
			debug("ldHeader($header)");
			Header($header);
			$ARCurrent->ldHeaders[strtolower($header)]=$header;
		} else {
			debug("Headers already sent, couldn't send $header","all");
		}
		return $result;
	}

	function ldSetClientCache($cache_on, $expires=0, $modified=0) {
		global $ARCurrent;
		$now=time();
		if ($cache_on) {
			if (!$expires) {
				$expires=$now+1800;
			}
			if (!$modified) {
				$modified=$now;
			}
			ldHeader("Pragma: cache");
			ldHeader("Cache-control: cache");
			ldHeader("Expires: ".gmdate(DATE_RFC1123,$expires));
			$result=ldHeader("Last-Modified: ".gmdate(DATE_RFC1123,$modified));
		} else {
			if (!$modified) {
				$modified=time();
			}
			ldHeader("Pragma: no-cache");
			ldHeader("Cache-control: must-revalidate, max-age=0, private");
			ldHeader("Expires: ".gmdate(DATE_RFC1123,$expires));
			$result=ldHeader("Last-Modified: ".gmdate(DATE_RFC1123,$modified));
		}
		return $result;
	}

	function ldSetContent($mimetype, $size=0) {
	/*
		$result=ldHeader("Content-Type: ".$mimetype);
		if ($size) {
			$result=ldHeader("Content-Length: ".$size);
		}
		return $result;
	*/
	}

	function ldGetServerVar($server_var) {

		return $_SERVER[$server_var];
	}

	function ldGetClientVar($client_var) {
		// not all environment variables should be disclosed
		switch($client_var) {
			case "REMOTE_ADDR": $result = getenv("REMOTE_ADDR"); break;
			case "HTTP_USER_AGENT": $result = getenv("HTTP_USER_AGENT"); break;
			case "HTTP_ACCEPT": $result = getenv("HTTP_ACCEPT"); break;
			case "HTTP_ACCEPT_LANGUAGE": $result = getenv("HTTP_ACCEPT_LANGUAGE"); break;
			default: $result = false; break;
		}
		return $result;
	}

	function ldDisablePostProcessing() {
		// dummy function
		return false;
	}

	function ldGetRequestedHost() {
		// dummy function
	}
?>