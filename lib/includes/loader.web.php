<?php
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
				$fp=fopen($store->files."cache/".$file, "w");
				fwrite($fp, $image);
				fclose($fp);
				$fp=fopen($store->files."cacheheaders/".$file, "w");
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
			@mkdir($curr, 0755);
			$dir=strtok("/");
		}
	}

	function ldSetCredentials($login, $password) {
	global $ARCurrent, $AR, $ARCookie;

		debug("ldSetCredentials($login, [password])","object");
		if (!$ARCurrent->session || ($AR->user->data->login!=$login)) {
			// start a new session when there is no session yet, or
			// when a user uses a new login. (su)
			ldStartSession();
		}
		$ARCurrent->session->put("ARLogin",$login);
		$ARCurrent->session->put("ARPassword",$password);
		$cookie=unserialize($ARCookie);
		// FIXME: now clean up the cookie, remove old sessions
		@reset($cookie) {
		while (list($sessionid, $data)=each($cookie)) {
			if (!$ARCurrent->session->sessionstore->exists("/$sessionid/")
				|| $ARCurrent->session->sessionstore->call("system.expired.phtml",,
					$ARCurrent->session->sessionstore->get("/$sessionid/")) {
				unset($cookie[$sessionid]);
			}
		}	
		
		$cookie[$ARCurrent->session->id]['login']=$login;
		$cookie[$ARCurrent->session->id]['timestamp']=time();
		$cookie[$ARCurrent->session->id]['check']="{".md5($password.$ARCurrent->session->id)."}";
		$ARCookie=serialize($cookie);
		setcookie("ARCookie",$ARCookie, 0, '/');
	}

	function ldCheckCredentials($login, $password) {
	global $ARCurrent, $AR, $ARCookie;
		debug("ldGetCredentials()","object");
		$result=false;
		$cookie=unserialize($ARCookie);
		if ($login==$cookie[$ARCurrent->session->id]['check'] && ($saved=$cookie[$ARCurrent->session->id]['check'])) {
			$check="{".md5($password.$ARCurrent->session->id)."}";
			if ($check==$saved) {
				$result=true;
			}
		}
		return $result;
	}

	function ldRedirect($uri) {
		$result=false;
		if (!Headers_sent()) {
			$result=true;
			Header("Location: $uri");
		}
		return $result;
	}

	function ldHeader($header) {
		$result=false;
		if (!Headers_sent()) {
			$result=true;
			Header($header);
		}
		return $result;
	}

	function ldSetClientCache($cache_on, $expires=0, $modified=0) {
		$result=false;
		if (!Headers_sent()) {
			$result=true;
			if ($cache_on) {
				if (!$expires) {
					$expires=time()+1800;
				}
				Header("Expires: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$expires));
			} else {
				if (!$modified) {
					$modified=time();
				}
				Header("Pragma: no-cache");
				Header("Cache-control: no-store, no-cache, must-revalidate, max-age=0");
				Header("Expires: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$expires));
				Header("Last-Modified: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$modified));
				Header("Cache-control: private");
			}
		}
		return $result;
	}

	function ldSetContent($mimetype, $size=0) {
		$result=false;
		if (!Headers_sent()) {
			$result=true;
			Header("Content-type: ".$mimetype);
			if ($size) {
				Header("Content-Length: ".$size);
			}
		}
		return $result;
	}

?>