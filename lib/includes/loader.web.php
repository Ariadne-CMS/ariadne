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
		global $DB, $DB_INDENT;
	 	if ($DB["level"]>=$DB[$level]) {
			if ($indent=="OUT") {
				$DB_INDENT=substr($DB_INDENT,0,-2);
			}
			echo "$DB_INDENT<b>$level::$text</b><BR>\n";
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


	function ldAccessDenied($path, $message) {
	global $ARCurrent, $store;

		if (!$ARCurrent->arLoginSilent) {
			$store->call("user.login.html", 
								Array( "arLoginMessage" => $message ),
								$store->get($path) );
		}
	}

	function ldSetRoot($session='', $nls='') {
	global $store, $AR, $ARCurrent;

		$root=$AR->root;
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
	global $ARCurrent, $ariadne;

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
		if (!$ARCurrent->session || ($ARCurrent->session->get("ARLogin")!=$login)) {
			// start a new session when there is no session yet, or
			// when a user uses a new login. (su)
			ldStartSession();
		}
		$ARCurrent->session->put("ARLogin",$login);
		$ARCurrent->session->put("ARPassword",$password);
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
			} else if (@current($ARCurrent->session->sessionstore->call("system.expired.phtml","",
						$ARCurrent->session->sessionstore->get("/$sessionid/")))) {
				unset($cookie[$sessionid]);
			}
		}
		$cookie[$ARCurrent->session->id]['login']=$login;
		$cookie[$ARCurrent->session->id]['timestamp']=time();
		$cookie[$ARCurrent->session->id]['check']="{".ARCrypt($password.$ARCurrent->session->id)."}";
		$ARCookie=serialize($cookie);
		setcookie("ARCookie",$ARCookie, 0, '/');
	}

	function ldCheckCredentials($login, $password) {
	global $ARCurrent, $AR, $HTTP_COOKIE_VARS;
		/* 
			FIXME:
			this is a hack: php 4.0.3pl1 (and up?) runs 'magic_quotes' on
			cookies put in $HTTP_COOKIE_VARS which will cause unserialize
			to not function correctly.
		*/
		$ARCookie = stripslashes($HTTP_COOKIE_VARS["ARCookie"]);
		debug("ldCheckCredentials()","object");
		$result=false;
		$cookie=unserialize($ARCookie);
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

	function ldRedirect($uri) {
		return ldHeader("Location: $uri");
	}

	function ldHeader($header) {
	global $ARCurrent;

		$result=false;
		if (!Headers_sent()) {
			$result=true;
			Header($header);
			$ARCurrent->ldHeaders[]=$header;			
		} else {
			debug("Headers already sent, couldn't send $header","all");
		}
		return $result;
	}

	function ldSetClientCache($cache_on, $expires=0, $modified=0) {
		if ($cache_on) {
			if (!$expires) {
				$expires=time()+1800;
			}
			$result=ldHeader("Expires: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$expires));
		} else {
			if (!$modified) {
				$modified=time();
			}
			ldHeader("Pragma: no-cache");
			ldHeader("Cache-control: no-store, no-cache, must-revalidate, max-age=0");
			ldHeader("Expires: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$expires));
			ldHeader("Last-Modified: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$modified));
			$result=ldHeader("Cache-control: private");
		}
		return $result;
	}

	function ldSetContent($mimetype, $size=0) {
		$result=ldHeader("Content-type: ".$mimetype);
		if ($size) {
			$result=ldHeader("Content-Length: ".$size);
		}
		return $result;
	}

?>