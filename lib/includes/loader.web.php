<?php
    /******************************************************************
     loader.web.php                                        Muze Ariadne
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
	include_once($store_config['code']."includes/loader.web.auth.php");
	include_once($store_config['code']."objects/pobject.phtml");
	
	if (
			is_array($AR->loader->web['AllowedMethods']) &&
			!(in_array(strtoupper($_SERVER['REQUEST_METHOD']), $AR->loader->web['AllowedMethods']))
		) {
		ldHeader("HTTP/1.1 405 Not allowed");
		exit;
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

		require_once($store->get_config("code")."modules/mod_mimemagic.php");
		$result = Array();
		$http_post_file = Array('name' => '', 'type' => '', 'tmp_name' => '', 'error' => '', 'size' => '');
		$subfields = explode('[', $field);
		$field = array_shift($subfields);
		foreach ($http_post_file as $key => $value) {
			$value = &$_FILES[$field][$key];
				foreach ($subfields as $subfield) {
					$subfield = substr($subfield, 0, -1);
					$value = &$value[$subfield];
				}
			$http_post_file[$key] = $value;
		}
		if ($subfield) {
			$field = $subfield;
		}
		$file_temp = $http_post_file['tmp_name'];
		$file = $http_post_file['name'];
		if ($file && is_uploaded_file($file_temp)) {
			list($inf, $inftp) = virusscan($file_temp);
			if($inf) {
				virusclean($file_temp);
				// This is duplicate in some cases. Should be a bit cleaned up.
				$error = sprintf($ARnls["err:fileuploadvirus"], $inftp);
			} else {
				// new file uploaded -> save it before PHP deletes it
				$file_artemp=tempnam($store->get_config("files")."temp","upload");
				if (move_uploaded_file($file_temp, $file_artemp)) {
					// now make the new values available to wgWizKeepVars()
					$result[$field]=$file;
					$result[$field."_temp"]=basename($file_artemp);
					$result[$field."_size"]=(int)$http_post_file['size'];
					$type = get_mime_type($file_artemp);
					$ext  = substr($file, strrpos($file, '.')); 
					if (!$type) {
						$type = get_mime_type($file, MIME_EXT);
					}
					$result[$field."_type"] = get_content_type($type, $ext);
				}
			}
		}
		return $result;
	}

	function ldOnFinish() {
	global $ARCurrent, $store;
		$error = error_get_last();
		if ($error['type'] == 1) { // Fatal error
			$context = pobject::getContext();
			if ($context['scope'] == 'pinp') {
				pobject::pinpErrorHandler($error['type'], $error['message'], $error['file'], $error['line'], null);
			}
		}
		if ($ARCurrent->session) {
			$ARCurrent->session->save();
		}
		if ($store) {
			$store->close();
		}
	}

	function ldObjectNotFound($requestedpath, $requestedtemplate) {
	global $store, $AR, $ARCurrent,$args;
		$path=$requestedpath;
		if (!$path) {
			error("Empty path requested with template: $requestedtemplate");
		} else {
			while ($path!=$prevPath && !$store->exists($path)) {
				$prevPath=$path;
				$path=$store->make_path($path, "..");
			}
			if(count($ARCurrent->arCallStack) == 0) {
				$arCallArgs = $args;
			} else {
				$arCallArgs = @array_pop($ARCurrent->arCallStack);
				@array_push($ARCurrent->arCallStack, $arCallArgs);
			}
			if ($prevPath==$path) {
				error("Database is not initialised, please run <a href=\"".$AR->dir->www."install/install.php\">the installer</a>");
			} else {
				// make sure the config check has been run:
				$ob = current( $store->call('system.get.phtml', array(), $store->get($path) ) );
				$ob->loadConfig(); //CheckConfig();

				$ob->pushContext( array(
					"arSuperContext" => Array(),
					"arCurrentObject" => $ob,
					"scope" => "php",
					"arCallFunction" => $requestedtemplate
				) );

				$requestedArgs = $arCallArgs;

				$eventData = new object();
				$eventData->arCallPath = $requestedpath;
				$eventData->arCallFunction = $requestedtemplate;
				$eventData->arCallArgs = $arCallArgs;
				$eventData = ar_events::fire( 'onnotfound', $eventData );
				if ( $eventData ) {
					// no results: page couldn't be found, show user definable 404 message
					$arCallArgs = (array) $eventData->arCallArgs;
					$myarCallArgs = array_merge($arCallArgs, 
						Array(	"arRequestedPath" => $requestedpath,
						 		"arRequestedTemplate" => $requestedtemplate,
								"arRequestedArgs" => $requestargs
						)
					);
					$store->call("user.notfound.html",$myarCallArgs,
						 $store->get($path));
				}

				$ob->popContext();
			}
		}
	}




	function ldSetRoot($session='', $nls='') {
	global $store, $AR, $ARCurrent, $root, $rootoptions;

		$root=$AR->root;
		$rootoptions="";
		if ($session && !$AR->hideSessionIDfromURL) {
			$rootoptions.="/-".$session."-";
			$ARCurrent->session->id=$session;
		}
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
	global $ARCurrent, $ARnls;

		$session=$ARCurrent->session->id;
		ldSetRoot($session, $nls);
		
		if( is_object( $ARnls ) ) {
			$ARnls->setLanguage($nls);
		}
	}

	function ldSetSession($session='') {
	global $ARCookie, $AR, $ARCurrent;

		$nls=$ARCurrent->nls;
		if ($AR->hideSessionIDfromURL) {
			$check = ldGetCredentials();
			if (!$check[$ARCurrent->session->id]) {
				$cookie = Array();
				$cookie[$ARCurrent->session->id]['timestamp']=time();
				$ARCookie=serialize($cookie);
				debug("setting cookie ($ARCookie)");
				header('P3P: CP="NOI CUR OUR"');
				setcookie("ARCookie",$ARCookie, 0, '/');
			}
		}
		ldSetRoot($session, $nls);
	}
 
	function ldStartSession($sessionid='') {
	global $ARCurrent, $AR, $ariadne;

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

				$imagetemp   = tempnam($store->get_config("files")."cache/".$path."/","ARCacheImage");
				$headertemp  = tempnam($store->get_config("files")."cacheheaders/".$path."/","ARCacheImage");

				$fileimage   = $store->get_config("files")."cache".$file;
				$fileheaders = $store->get_config("files")."cacheheaders".$file;

				$fpi=@fopen($imagetemp, "wb");
				$fph=@fopen($headertemp, "wb");

				if($fpi && $fph) {
					fwrite($fph, $headers);
					fclose($fph);
					fwrite($fpi, $image);
					fclose($fpi);

					rename($headertemp,$fileheaders);
					rename($imagetemp,$fileimage);
				} else {
					if($fpi) {
						fclose($fpi);
						unlink($fileimage);
					}
					if($fph) {
						fclose($fph);
						unlink($fileheaders);
					}
				}
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

			/* 
				FIXME:
				this is a hack: php 4.0.3pl1 (and up?) runs 'magic_quotes' on
				cookies put in $_COOKIE which will cause unserialize
				to not function correctly.
			*/
			$ARUserCookie = stripslashes($_COOKIE[$cookiename]);
			debug("ldGetUserCookie() = $ARUserCookie","object");
			$cookie=unserialize($ARUserCookie);
		}
		return $cookie;
	}

	function ldSetConsentedCookie($cookie, $cookiename="ARUserCookie", $expire=0, $path="/", $domain="", $secure=0) {
		// Sets a cookie, but only when ARCookieConsent has been given.
		return ldSetUserCookie($cookie, $cookiename, $expire, $path, $domain, $secure, true);
	}

	function ldSetUserCookie($cookie, $cookiename="ARUserCookie", $expire=0, $path="/", $domain="", $secure=0, $consentneeded=false) {
		$result = false;

		$cookieconsent = ldGetUserCookie("ARCookieConsent");
		if (!$consentneeded || ($cookieconsent == true)) { // Only set cookies when consent has been given, or no consent is needed for this cookie.
			if( $cookiename != "ARCookie") {
				$ARUserCookie=serialize($cookie);
				debug("ldSetUserCookie(".$ARUserCookie.")","object");
				header('P3P: CP="NOI CUR OUR"');
				$result = setcookie($cookiename,$ARUserCookie, $expire, $path, $domain, $secure);
			}
		} else {
			debug("ldSetUserCookie: no consent. (".$ARUserCookie.")","object");
		}
		return $result;
	}

	function ldRedirect($uri) {
		return ldHeader("Location: $uri");
	}

	function ldHeader($header,$replace=true) {
	global $ARCurrent;
		$result=false;
		if ( !Headers_sent() && !$ARCurrent->arNoHeaders ) {
			$result=true;
			list($key,$value) = explode(':',$header,2);
			Header($header,$replace);
			if($replace){
				$ARCurrent->ldHeaders[strtolower($key)]=$header;
			} else {
				$ARCurrent->ldHeaders[strtolower($key)].=$header;
			}
		}
		return $result;
	}

	function ldSetClientCache( $cache_on, $expires = null, $modified = null ) {
		global $ARCurrent;
		$now = time();
		if ( !isset($modified) ) {
			$modified = $now;
		}
		if ($cache_on) {
			if ( !isset($expires) ) {
				$expires = $now + 1800;
			}
			$result = ldHeader("Pragma: cache");

			// Give the client the max-age
			$maxage = $expires - $now;
			ldHeader("Cache-control: public, max-age=$maxage");
		} else {
			if ( !isset($expires) ) {
				$expires = 0;
			}
			$result = ldHeader("Pragma: no-cache");
			ldHeader("Cache-control: no-store, no-cache, must-revalidate, max-age=0, private");
		}
		if ( $expires !== false ) {
			ldHeader("Expires: ".gmdate(DATE_RFC1123, $expires));
		}
		if ( $modified !== false ) {
			ldHeader("Last-Modified: ".gmdate(DATE_RFC1123, $modified));
		}
		return $result;
	}

	function ldSetContent($mimetype, $size=0) {
		global $ARCurrent;
		$result=ldHeader("Content-Type: ".$mimetype);
		$ARCurrent->arContentTypeSent = true;
		if ($size) {
			$result=ldHeader("Content-Length: ".$size);
		}
		return $result;
	}

	function ldGetServerVar($server_var = "") {
		if (!$server_var) {
			return $_SERVER;
		}
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

?>