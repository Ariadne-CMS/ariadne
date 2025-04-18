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

	include_once($store_config['code']."includes/loader.web.auth.php");
	include_once($store_config['code']."objects/pobject.phtml");

	function ldCheckAllowedMethods($method = null) {
		global $AR;
		if (
				isset($AR->loader) &&
				is_array($AR->loader->web['AllowedMethods']) && isset($method) &&
				!(in_array(strtoupper($method), $AR->loader->web['AllowedMethods']))
		) {
			header("HTTP/1.1 405 Method Not Allowed");
			exit(1);
		}
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

	function ldRegisterFile($field, &$error) {
	global $ARnls, $store;
		if (!isset($field)) {
			$field = "file";
		}

		require_once($store->get_config("code")."modules/mod_mimemagic.php");
		$result = Array();
		$http_post_file = Array('name' => '', 'type' => '', 'tmp_name' => '', 'error' => '', 'size' => '');
		$subfields = explode('[', $field);
		$field = array_shift($subfields);
		$subfield = false;
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

		if ($http_post_file['error']) {
			switch ($http_post_file['error']) {
				case UPLOAD_ERR_INI_SIZE:
					$error = $ARnls['ariadne:err:upload_ini_size']; // "The uploaded file exceeds the upload_max_filesize directive in php.ini";
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$error = $ARnls['ariadne:err:upload_form_size']; // The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
					break;
				case UPLOAD_ERR_PARTIAL:
					$error = $ARnls['ariadne:err:upload_partial']; // "The uploaded file was only partially uploaded";
					break;
				case UPLOAD_ERR_NO_FILE:
					// Note: this is not an error
					//$error = $ARnls['ariadne:err:upload_no_file']; // No file was uploaded";
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$error = $ARnls['ariadne:err:upload_no_tmp_dir']; // "Missing a temporary folder";
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$error = $ARnls['ariadne:err:upload_cant_write']; // "Failed to write file to disk";
					break;
				case UPLOAD_ERR_EXTENSION:
					$error = $ARnls['ariadne:err:upload_extension']; // "File upload stopped by extension";
					break;

				default:
					$error = sprintf($ARnls['ariadne:err:upload_error'], $http_post_file['error']); // "Unknown upload error %s";
					break;
			}
			return $result;
		}

		$file_temp = $http_post_file['tmp_name'];
		$file = $http_post_file['name'];
		if ($file && is_uploaded_file($file_temp)) {
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
		return $result;
	}

	function ldOnFinish() {
	global $ARCurrent, $store;
		$error = error_get_last();
		if (($error['type']??null) == 1) { // Fatal error
			$context = pobject::getContext();
			if ( ( $context['scope'] ?? null ) == 'pinp' ) {
				pobject::pinpErrorHandler($error['type'], $error['message'], $error['file'], $error['line'], null);
			}
		}
		if (isset($ARCurrent->session)) {
			$ARCurrent->session->save();
		}
		if ($store) {
			$store->close();
		}
	}

	function ldObjectNotFound($requestedpath, $requestedtemplate, $requestedargs = null ) {
	global $store, $AR, $ARCurrent, $args;
		$path=$requestedpath;
		if ( !$requestedargs ) {
			$requestedargs = $args;
		}
		if (!$path) {
			error("Empty path requested with template: $requestedtemplate");
		} else {
			$prevPath = null;
			while ($path !== $prevPath && !$store->exists($path)) {
				$prevPath = $path;
				$path     = $store->make_path($path, "..");
			}
			if(count($ARCurrent->arCallStack) == 0) {
				$arCallArgs = $requestedargs;
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

				$eventData = new baseObject();
				$eventData->arCallPath = $requestedpath;
				$eventData->arCallFunction = $requestedtemplate;
				$eventData->arCallArgs = $arCallArgs;
				$eventData = ar_events::fire( 'onnotfound', $eventData );
				if ( $eventData ) {
					// no results: page couldn't be found, show user definable 404 message
					$arCallArgs = (array) $eventData->arCallArgs;
					$requestedargs = $arCallArgs;
					$myarCallArgs = array_merge($arCallArgs,
						Array(	"arRequestedPath" => $requestedpath,
						 		"arRequestedTemplate" => $requestedtemplate,
								"arRequestedArgs" => $requestedargs
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

		$session=$ARCurrent->session->id ?? null;
		ldSetRoot($session, $nls);

		if( is_object( $ARnls ) ) {
			$ARnls->setLanguage($nls);
		}
	}

	function ldGetRequestedHost() {
	global $AR;
		$requestedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? null;
		if (!$requestedHost) {
			$requestedHost = $_SERVER['HTTP_HOST'];
		}
		$protocol = 'http://';
		if (($_SERVER['HTTPS'] ?? null)=='on') {
			$protocol = 'https://';
		}
		return $protocol . $requestedHost;
	}

	function ldSetSession($session='') {
		global $AR, $ARCurrent;
		$nls=$ARCurrent->nls;
		if ($AR->hideSessionIDfromURL) {
			$cookies = (array) ldGetCredentials();
			$https = ($_SERVER['HTTPS']=='on');
			if( !isset($cookies[$ARCurrent->session->id])  && $ARCurrent->session->id !== 0) {
				$data = array();
				$data['timestamp']=time();
				$cookie=ldEncodeCookie($data);
				$cookiename = "ARSessionCookie[".$ARCurrent->session->id."]";
				debug("setting cookie (".$ARCurrent->session->id.")(".$cookie.")");
				header('P3P: CP="NOI CUR OUR"');
				setcookie('ARCurrentSession', $ARCurrent->session->id, 0, '/', false, $https, true);
				setcookie($cookiename,$cookie, 0, '/', false, $https, true);
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

	function ldGetCookieSession() {
		return $_COOKIE['ARCurrentSession'];
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
					$imagesize = fwrite($fpi, $image);
					fclose($fpi);

					if ( !@touch($imagetemp, $time)) { // silently ignore error when touch fails;
						debug("ldSetCache: ERROR: couldn't touch image","object");
					}

					if (filesize($imagetemp) == $imagesize ) {
						rename($headertemp,$fileheaders);
						rename($imagetemp,$fileimage);
					} else {
						@unlink($imagetemp);
						@unlink($headertemp);
					}
				} else {
					if($fpi) {
						fclose($fpi);
						@unlink($imagetemp);
					}
					if($fph) {
						fclose($fph);
						@unlink($headertemp);
					}
				}
			}
		}
	}

	function ldMkDir($dir) {
	global $store;

		debug("ldMkDir($dir)","object");
		$curr=$store->get_config("files");
		@mkdir($curr.'/'.$dir, 0755, true);
	}

	function ldGetUserCookie($cookiename="ARUserCookie") {
		$cookie = false;

		if( ($_COOKIE[$cookiename] ?? null) && !($cookiename == "ARSessionCookie") && !($cookiename == 'ARCurrentSession') ) {
			$ARUserCookie = $_COOKIE[$cookiename];
			debug("ldGetUserCookie() = $ARUserCookie","object");
			$cookie = json_decode($ARUserCookie,true);
			if ($cookie === null) {
				$cookie = unserialize($ARUserCookie);
			}
		}
		return $cookie;
	}

	function ldSetConsentedCookie($cookie, $cookiename="ARUserCookie", $expire=0, $path="/", $domain="", $secure=0) {
		// Sets a cookie, but only when ARCookieConsent has been given.
		return ldSetUserCookie($cookie, $cookiename, $expire, $path, $domain, $secure, true);
	}

	function ldSetUserCookie($cookie, $cookiename="ARUserCookie", $expire=0, $path="/", $domain="", $secure=0, $consentneeded=false) {
		$result = false;

		if (substr($cookiename, 0, strlen('ARSessionCookie'))=='ARSessionCookie' || $cookiename=='ARCurrentSession' ) {
			return false;
		}

		$cookieconsent = ldGetUserCookie("ARCookieConsent");
		if (!$consentneeded || ($cookieconsent == true)) { // Only set cookies when consent has been given, or no consent is needed for this cookie.
			if( $cookiename != "ARSessionCookie" && $cookiename != "ARCurrentSession" ) {
				$ARUserCookie=json_encode($cookie);
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
		$ARCurrent->arNoHeaders = $ARCurrent->arNoHeaders ?? null;
		
		if ( !Headers_sent() && !$ARCurrent->arNoHeaders ) {
			$result=true;
			$headerInfo = explode(':',$header,2);
			$key = $headerInfo[0];
			$value = $headerInfo[1] ?? null;
			Header($header,$replace);
			if($replace){
				$ARCurrent->ldHeaders[strtolower($key)]=$header;
			} else {
				$ARCurrent->ldHeaders[strtolower($key)].=$header;
			}
		}
		return $result;
	}

	function ldSetBrowserCache( $settings ) {
		if ($settings === false) {
			return ldHeader("Cache-control: no-store, no-cache, must-revalidate, max-age=0, private");
		}
		$cacheControl = "Cache-control: ";
		$cacheControl .= ($settings['browserCachePrivate'] ? "private" : "public");
		$cacheControl .= ($settings['browserCacheNoStore'] ? ", no-store" : "");
		$cacheControl .= ($settings['browserCacheNoCache'] ? ", no-cache" : "");
		$cacheControl .= ($settings['browserCacheMustRevalidate'] ? ", must-revalidate" : "");
		$cacheControl .= ($settings['browserCacheProxyRevalidate'] ? ", proxy-revalidate" : "");
		$cacheControl .= ($settings['browserCacheNoTransform'] ? ", no-transform" : "");
		$cacheControl .= ($settings['browserCacheMaxAge'] ? ", max-age=" . $settings['browserCacheMaxAge'] : ", max-age=0");
		$cacheControl .= ($settings['browserCacheSMaxAge'] ? ", s-max-age=" . $settings['browserCacheMaxAge'] : "");

		ldHeader($cacheControl);
	}

	function ldSetClientCache( $cache_on, $expires = null, $modified = null ) {
		$now = time();
		if ($cache_on) {
			if ( !isset($expires) ) {
				$expires = $now + 1800;
			}

			// Give the client the max-age
			$maxage = $expires - $now;
			ldHeader("Cache-control: public, max-age=$maxage, must-revalidate");
			ldHeader("X-Ariadne-Expires: $expires");
		} else {
			ldHeader("Cache-control: no-store, no-cache, must-revalidate, max-age=0, private");
		}
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
		return $_SERVER[$server_var] ?? null;
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
		global $ARCurrent,$ldXSSProtectionActive,$ldOutputBufferActive;

		// only disable the cache when we don't have xss protection active
		if($ldXSSProtectionActive !== true  && $ldOutputBufferActive === true) {
			/*
				kill the innermost output buffer, if there is any other buffering layers active
				in the end this will remove the outputbuffer used in the loader
			*/

			ob_end_flush();

			// because we are forceing the output, we can't cache it anymore, disable it for safety sake
			$ARCurrent->arDontCache = true;
			$ldOutputBufferActive=false;
			return true;
		} else if ($ldXSSProtectionActive === true) {
			return false;
		} else if ($ldOutputBufferActive === false) {
			return 2;
		}
	}

	function ldProcessCacheControl() {
		global $ARCurrent;
		if ( !isset( $ARCurrent->RequestCacheControl ) ) {
			$ARCurrent->RequestCacheControl = [];
		}
		if (isset($_SERVER["HTTP_CACHE_CONTROL"])) {
			$cc = $_SERVER["HTTP_CACHE_CONTROL"];
			$parts = explode(',', $cc);
			foreach($parts as $part) {
				$part = trim ($part);
				$parts = explode("=", $part, 2);
				$key = $parts[0];
				$value = $parts[1] ?? null;
				
				// list($key,$value) = explode('=', $part,2);
				$key = trim($key);
				switch($key) {
					case "no-cache":
					case "no-store":
					case "no-transform":
					case "only-if-cached":
						$ARCurrent->RequestCacheControl[$key] = true;
						break;
					case "max-age":
					case "max-stale":
					case "min-fresh":
						$value = (int)filter_var($value,FILTER_SANITIZE_NUMBER_INT);
						$ARCurrent->RequestCacheControl[$key] = $value;
						break;
					default:
						// do nothing
						break;
				}
			}
		}
	}

	function ldGatherXSSInput(&$xss, $input) {
		if (is_array($input)) {
			foreach ($input as $value) {
				ldGatherXSSInput($xss, $value);
			}
		} else {
			$input = (string)$input;
			if (strlen($input) > 10) {
				if (preg_match('/[\'"<>]/', $input)) {
					$xss[strlen($input)][$input] = $input;
				}
			}
		}
	}

	function ldCheckAllowedTemplate($template) {
		// Check if a template is allowed to be called directly from the URL.
		if ($template == "system.list.folders.json.php") {
			// FIXME: this template is used to fetch folders in explore - it should be renamed to explore.list.folders.json.php;
			return true;
		} else if ($template == "system.list.objects.json.php") {
			// FIXME: this template is used to fetch objects in explore - it should be renamed to explore.list.objects.json.php;
			return true;
		} else if (preg_match('/^(system|ftp|webdav|soap)\./', $template)) {
			// Disallow all direct calls to system.*, ftp.*, webdav.*, soap.* templates;
			// FTP, webdav, soap should use their own loader instead.
			return false;
		}

		return true;
	}

	if ( !isset( $ARCurrent->refreshCacheOnShutdown ) ) {
		$ARCurrent->refreshCacheOnShutdown = false;
	}

	function ldCacheRequest($AR_PATH_INFO=null) {
		ob_start();

		global $ARCurrent;
		$ARCurrent->refreshCacheOnShutdown = true;
		ldProcessRequest($AR_PATH_INFO);
		ob_end_clean();
	}

	function ldProcessRequest($AR_PATH_INFO=null) {
		global $AR;
		global $ARCurrent;
		global $ARnls;
		global $store_config;
		global $auth_config;
		global $cache_config;
		global $store;
		global $context;
		global $DB;
		global $path;
		global $function;
		global $nls;

		$writecache = false;

		// go check for a sessionid
		$root=$AR->root;
		$session_id=0;
		$re="^/-(.{4})-/";

		$originalPathInfo = $AR_PATH_INFO; // Store this to pass to the refresh cache on shutdown function;

		if (preg_match( '|'.$re.'|' , $AR_PATH_INFO , $matches )) {
			$session_id=$matches[1];
			$AR_PATH_INFO=substr($AR_PATH_INFO,strlen($matches[0])-1);
			$AR->hideSessionIDfromURL=false;
		} elseif ($AR->hideSessionIDfromURL) {
			$cookies = (array) ldGetCredentials();
			$current = ldGetCookieSession();
			if ( array_key_exists( $current, $cookies ) ) {
				$session_id = $current;
			}
		} elseif (isset($_SERVER['PHP_AUTH_USER'])) {
			$cookies = (array) ldGetCredentials();
			$current = ldGetCookieSession();
			if ( array_key_exists( $current, $cookies ) ) {
				$session_id = $current;
			}
		}

		$AR->login="admin";


		// look for the template
		$split=strrpos($AR_PATH_INFO, "/");
		$path=substr($AR_PATH_INFO,0,$split+1);
		$function=substr($AR_PATH_INFO,$split+1);
		if (!$function ) {
			if (!isset($arDefaultFunction) || $arDefaultFunction == '' ) {
				$arDefaultFunction="view.html";
			}
			$function=$arDefaultFunction;
			if (isset($arFunctionPrefix) && $arFunctionPrefix != '' ) {
				$function=$arFunctionPrefix.$function;
			}
			$AR_PATH_INFO.=$function;
		}


		/*
			start output buffering
		*/
		ob_start();
		global $ldOutputBufferActive;
		$ldOutputBufferActive = true;
		ob_implicit_flush(0);

		// look for the language
		$split=strpos(substr($AR_PATH_INFO, 1), "/");
		$ARCurrent->nls=substr($path, 1, $split);
		if (!isset($AR->nls->list[$ARCurrent->nls]) ) {
			// not a valid language
			$ARCurrent->nls="";
			$nls=$AR->nls->default;
			// but we can find out if the user has any preferences
			preg_match_all("%([a-zA-Z]{2}|\\*)[a-zA-Z-]*(?:;q=([0-9.]+))?%", $_SERVER["HTTP_ACCEPT_LANGUAGE"] ?? "", $regs, PREG_SET_ORDER);
			$ARCurrent->acceptlang=array();
			$otherlangs=array();
			$otherq=false;
			foreach ($regs as $reg) {
				if (!isset($reg[2])) {
					$reg[2]=1;
				}
				if ($reg[1]=="*") {
					$otherq=$reg[2];
				} else if ($AR->nls->list[$reg[1]]) {
					$otherlangs[]=$reg[1];
					$ARCurrent->acceptlang[$reg[1]]=$reg[2];
				}
			}
			if ($otherq !== false) {
				$otherlangs=array_diff(array_keys($AR->nls->list), $otherlangs);
				foreach ($otherlangs as $lang) {
					$ARCurrent->acceptlang[$lang]=$otherq;
				}
			}
			arsort($ARCurrent->acceptlang);
		} else {
			// valid language
			$path=substr($path, $split+1);
			// ldSetNls($ARCurrent->nls);
			$nls=$ARCurrent->nls;
		}

		$args=array_merge($_GET,$_POST);

		// instantiate the store
		$inst_store = $store_config["dbms"]."store";
		$store=new $inst_store($root,$store_config);

//		$AR->user = current( $store->call('system.get.phtml', '', $store->get('/system/users/' . $AR->login ) ) );

		$ARLogin="admin";
		$AR->user=new baseObject;
		$AR->user->data=new baseObject;
		$AR->user->data->login="admin";
		$AR->user->data->name = "Administrator";

		//$store->rootoptions = $rootoptions;

//		if ($session_id) {
//			ldStartSession($session_id);
//		}

		// instantiate the ARnls
		if( $ARCurrent->nls != "" ) {
			ldSetNls($nls);
		}


		if (substr($function, -6)==".phtml") {
			// system template: no language check
			$ARCurrent->nolangcheck=1;
		}
		$ext = pathinfo($function, PATHINFO_EXTENSION);
		switch ( $ext ) {
			case 'css':
				ldSetContent('text/css; charset=utf-8');
			break;
			case 'js':
				ldSetContent('application/javascript; charset=utf-8');
			break;
			case 'json':
				ldSetContent('application/json; charset=utf-8');
			break;
			case 'xml':
				ldSetContent('text/xml; charset=utf-8');
			break;
			case 'jpg':
				ldSetContent('image/jpeg');
			break;
			case 'gif':
				ldSetContent('image/gif');
			break;
			case 'png':
				ldSetContent('image/png');
			break;
			case 'svg':
				ldSetContent('image/svg+xml');
			break;
			default:
				ldSetContent('text/html; charset=utf-8');
			break;
		}
		$ARCurrent->arContentTypeSent = true;

		register_shutdown_function("ldOnFinish");

		if ($function!==false) {
			// finally call the requested object
			unset($store->total);
			if (ldCheckAllowedTemplate($function) ) {
				$store->call($function, $args, $store->get($path));
				$writecache = true;
			}
			if (!$store->total) {
				ldObjectNotFound($path, $function, $args);
			}
		}

		if ($AR->ESI > 0) {
			// Prevent ESI from looping when the ESI result has ESI tags in them.
			// Reducing the AR->ESI number by 1 gives the flexibility to allow 2 or 3 ESI loops if desired.
			// Setting it to false would mean you only get 1 ESI loop, which might not be the desired effect.
			$AR->ESI = (int) $AR->ESI;
			$AR->ESI--;

			$image = ob_get_contents();
			ob_end_clean();
			include_once($store_config['code']."modules/mod_esi.php");
			$image = ESI::esiProcess($image);
			$image_len = strlen($image);

			if ($ARCurrent->arDontCache) {
				// FIXME: ook de cachetime 'niet cachen' uit het cachedialoog werkend maken...  || $ARCurrent->cachetime == 0) {
				ldSetBrowserCache(false);
			}
			ldHeader("Content-Length: ".$image_len);
			echo $image;
		}
	}

	function ldGetPutHandle() {
		$stdin = fopen("php://input", "r");
		return new ar_content_filesFile($stdin);
	}

