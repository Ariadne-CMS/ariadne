<?php
	// debugging functions.

	$DB["all"]=5;
	$DB["store"]=4;
	$DB["class"]=3;
	$DB["object"]=2;
	$DB["pinp"]=1;
	$DB["off"]=0;
	$DB["level"]=$DB["off"];

	$ERRMODE="htmljs"; // alternative: "text"/"html"/"js"

	function debug($text, $level="pinp", $indent="") {
	global $DB, $DB_INDENT, $AR;
		if ($DB["level"]>=$DB[$level]) {
			if ($indent=="OUT") {
				$DB_INDENT=substr($DB_INDENT,0,-2);
			}
			if ( ($AR->DEBUG == 'WEB') || ($AR->DEBUG == 'BOTH') ) {
				echo "$DB_INDENT<b>$level::$text</b><BR>\n";
			}
			if ( ($AR->DEBUG == 'SYSLOG') || ($AR->DEBUG == 'BOTH') ) {
				syslog(LOG_NOTICE,"(Ariadne) $level::$text");
			}
			flush();
			if ($indent=="IN") {
				$DB_INDENT.="  ";
			}
		}
	}

	function debugon($level="pinp") {
		global $DB;
		$DB["level"]=$DB[$level];
		debug("Debuglevel: $level");
	}

	function debugoff() {
		global $DB;
		debug("Debugging off.");
		$DB["level"]=$DB["off"];
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
	global $ARnls, $store, $HTTP_POST_FILES, $HTTP_POST_VARS;

		require_once($store->code."modules/mod_mimemagic.php");

		$result = false;

		$file_temp=$HTTP_POST_FILES[$field]['tmp_name'];
		$file=$HTTP_POST_FILES[$field]['name'];
		if ($file && is_uploaded_file($file_temp)) {
			list($inf, $inftp) = virusscan($file_temp);
			if($inf) {
				virusclean($file_temp);
				// This is duplicate in some cases. Should be a bit cleaned up.
				$error = sprintf($ARnls["err:fileuploadvirus"], $inftp);
			} else {
				// new file uploaded -> save it before PHP deletes it
				$file_artemp=tempnam($store->files."temp","upload");
				if (move_uploaded_file($file_temp, $file_artemp)) {
					// now make the new values available to wgWizKeepVars()
					$HTTP_POST_VARS[$field]=$file;
					$HTTP_POST_VARS[$field."_temp"]=substr($file_artemp,strlen($store->files."temp"));
					$HTTP_POST_VARS[$field."_size"]=$HTTP_POST_FILES[$field]['size'];
					$type = get_mime_type($file_artemp);
					if (!$type) {
						$type = get_mime_type($file, MIME_EXT);
					}
					$HTTP_POST_VARS[$field."_type"]=$type;
					$result = true;
				}
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
		while ($path!=$prevPath && !$store->exists($path)) {
			$prevPath=$path;
			$path=$store->make_path($path, "..");
		}
		if ($prevPath==$path) {
			error("Database is not initialised, please run <a href=\"".$AR->host.$AR->dir->www."install/install.php\">the installer</a>");
		} else {
			// no results: page couldn't be found, show user definable 404 message
			$store->call("user.notfound.html",
				 Array(	"arRequestedPath" => $requestedpath,
				 		"arRequestedTemplate" => $requestedtemplate ),
				 $store->get($path));
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
			$store->call("user.login.html", 
								$arCallArgs,
								$store->get($path) );
		}
	}

	function ldSetRoot($session='', $nls='') {
	global $store, $AR, $ARCurrent, $root, $rootoptions;

		$root=$AR->root;
		$rootoptions="";
		if ($session) {
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
	global $ARCurrent;

		$session=$ARCurrent->session->id;
		ldSetRoot($session, $nls);
	}

	function ldSetSession($session='') {
	global $ARCurrent;

		$nls=$ARCurrent->nls;
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
		$time=time()+($time*3600);
		if (!ereg("\.\.",$file)) {
			if ($image) {
				$path=substr($file, 1, strrpos($file, "/")-1);
				if (!file_exists($store->files."cache/".$path)) {
					ldMkDir("cache/".$path);
					ldMkDir("cacheheaders/".$path);
				}
				$fp=fopen($store->files."cache/".$file, "wb");
				fwrite($fp, $image);
				fclose($fp);
				$fp=fopen($store->files."cacheheaders/".$file, "wb");
				fwrite($fp, $headers);
				fclose($fp);
				if (!touch($store->files."cache/".$file, $time)) {
					debug("ldSetCache: ERROR: couldn't touch image","object");
				}
			}
		}
	}

	function ldMkDir($dir) {
	global $store;

		debug("ldMkDir($dir)","object");
		$dir=strtok($dir, "/");
		$curr=$store->files;
		while ($dir) {
			$curr.=$dir."/";
			debug("ldMkDir: $curr","all");
			@mkdir($curr, 0755);
			$dir=strtok("/");
		}
	}

	function ldSetCredentials($login, $password) {
	global $ARCurrent, $HTTP_COOKIE_VARS;

		$ARCookie = stripslashes($HTTP_COOKIE_VARS["ARCookie"]);

		debug("ldSetCredentials($login, [password])","object");
		if ($ARCurrent->session && 
			(!$ARCurrent->session->get("ARSessionActivated",1) ||
			!$ARCurrent->session->get("ARLogin"))) {

			/* use the same sessionid if the user didn't login before */
			ldStartSession($ARCurrent->session->id);
			$ARCurrent->session->put("ARLogin",$login);
			$ARCurrent->session->put("ARPassword",$password,1);
			$ARCurrent->session->put("ARSessionActivated",false,1);

		} else
		if (!$ARCurrent->session || 
			(!$ARCurrent->session->get("ARSessionActivated",1)) || 
			($ARCurrent->session->get("ARLogin")!=$login)) {
			// start a new session when there is no session yet, or
			// when a user uses a new login. (su)
			ldStartSession();
			$ARCurrent->session->put("ARLogin",$login);
			$ARCurrent->session->put("ARPassword",$password,1);
		} else
		if ($ARCurrent->session->get("ARSessionTimedout", 1)  &&
				ldCheckCredentials($login, $password) &&
				$ARCurrent->session->get("ARLogin") === $login &&
				$ARCurrent->session->get("ARPassword",1) === $password ) {
				/* cookie and login matches session */
				$ARCurrent->session->put("ARSessionTimedout", false, 1);
		}

		/* now save our session */
		$ARCurrent->session->save();

		$cookie=unserialize($ARCookie);
		// FIXME: now clean up the cookie, remove old sessions
		@reset($cookie);
		while (list($sessionid, $data)=@each($cookie)) {
			if (!$ARCurrent->session->sessionstore->exists("/$sessionid/")) {
				// don't just kill it, it may be from another ariadne installation
				if ($data['timestamp']<(time()-86400)) {
					// but do kill it if it's older than one day
					unset($cookie[$sessionid]);
				}
			} 
		}

		/* 
			only set a cookie when our session has not been timed out and
			our session is not active already.
		*/
		if (!$ARCurrent->session->get("ARSessionTimedout",1) &&
				!$ARCurrent->session->get("ARSessionActivated", 1)) {
			$cookie[$ARCurrent->session->id]['login']=$login;
			$cookie[$ARCurrent->session->id]['timestamp']=time();
			$cookie[$ARCurrent->session->id]['check']="{".ARCrypt($password.$ARCurrent->session->id)."}";
			$ARCookie=serialize($cookie);
			setcookie("ARCookie",$ARCookie, 0, '/');
		}
	}

	function ldGetCredentials() {
	global $HTTP_COOKIE_VARS;
		/* 
			FIXME:
			this is a hack: php 4.0.3pl1 (and up?) runs 'magic_quotes' on
			cookies put in $HTTP_COOKIE_VARS which will cause unserialize
			to not function correctly.
		*/
		$ARCookie = stripslashes($HTTP_COOKIE_VARS["ARCookie"]);
		debug("ldGetCredentials()","object");
		$cookie=unserialize($ARCookie);
		return $cookie;
	}

	function ldCheckCredentials($login, $password) {
	global $ARCurrent, $AR;
		debug("ldCheckCredentials()","object");
		$result=false;
		$cookie=ldGetCredentials();
		if ($login==$cookie[$ARCurrent->session->id]['login']
			&& ($saved=$cookie[$ARCurrent->session->id]['check'])) {
			$check="{".ARCrypt($password.$ARCurrent->session->id)."}";
			if ($check==$saved) {
				$result=true;
			} else {
				debug("login check failed","all");
			}
		} else {
			debug("wrong login or corrupted cookie","all");
		}			
		return $result;
	}

	function ldGetUserCookie() {
	global $HTTP_COOKIE_VARS;
		/* 
			FIXME:
			this is a hack: php 4.0.3pl1 (and up?) runs 'magic_quotes' on
			cookies put in $HTTP_COOKIE_VARS which will cause unserialize
			to not function correctly.
		*/
		$ARUserCookie = stripslashes($HTTP_COOKIE_VARS["ARUserCookie"]);
		debug("ldGetUserCookie() = $ARUserCookie","object");
		$cookie=unserialize($ARUserCookie);
		return $cookie;
	}

	function ldSetUserCookie($cookie) {
	global $HTTP_COOKIE_VARS;

		$ARUserCookie = stripslashes($HTTP_COOKIE_VARS["ARUserCookie"]);

		debug("ldSetUserCookie(".serialize($cookie).")","object");

		$ARUserCookie=serialize($cookie);
		setcookie("ARUserCookie",$ARUserCookie, 0, '/');
	}

	function ldRedirect($uri) {
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
			ldHeader("Expires: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$expires));
			$result=ldHeader("Last-Modified: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$modified));
		} else {
			if (!$modified) {
				$modified=time();
			}
			ldHeader("Pragma: no-cache");
			ldHeader("Cache-control: must-revalidate, max-age=0, private");
			ldHeader("Expires: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$expires));
			$result=ldHeader("Last-Modified: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$modified));
		}
		return $result;
	}

	function ldSetContent($mimetype, $size=0) {
		$result=ldHeader("Content-Type: ".$mimetype);
		if ($size) {
			$result=ldHeader("Content-Length: ".$size);
		}
		return $result;
	}

?>