<?php

	function ldSetCredentials($login) {
	global $ARCurrent, $AR, $LD_NO_SESSION_SUPPORT;

		if ($LD_NO_SESSION_SUPPORT) {
			debug("ldSetCredentials($login): no session support");
			return;
		}

		// Make sure the login is lower case. Because of the
		// numerous checks on "admin".
		$login = strtolower( $login );

		// this line is not needed
		//$ARCookie = stripslashes($_COOKIE["ARCookie"]);

		debug("ldSetCredentials($login)","object");

		if (!$ARCurrent->session) {
			ldStartSession();
		} else {
			/* use the same sessionid if the user didn't login before */
			ldStartSession($ARCurrent->session->id);
		}
		$ARCurrent->session->put("ARLogin", $login);

		/* create the session key */
		srand((double)microtime()*1000000);
		$session_key = md5(uniqid(rand(), true));

		$ARCurrent->session->put("ARSessionKey", $session_key, true);
		$ARCurrent->session->put("ARSessionTimedout", 0, 1);

		/* now save our session */
		$ARCurrent->session->save();

		$cookie=array();

		// FIXME: now clean up the cookie, remove old sessions
		// FIXME: cleaning up a empty array looks a bit silly ?
		/*
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
		 */

		$cookie[$ARCurrent->session->id]['login']=$login;
		$cookie[$ARCurrent->session->id]['timestamp']=time();
		$cookie[$ARCurrent->session->id]['check']="{".md5($login.$session_key)."}";
		$ARCookie=json_encode($cookie);
		debug("setting cookie ($ARCookie)");
		setcookie("ARCookie",$ARCookie, 0, '/');
	}

	function ldGetCredentials() {
		debug("ldGetCredentials()","object");
		$cookie=json_decode($ARCookie,true);
		if ($cookie === null) {
			$cookie=unserialize($ARCookie);
		}
		return $cookie;
	}

	function ldCheckCredentials($login) {
	global $ARCurrent, $AR;
		debug("ldCheckCredentials($login)","object");
		$result=false;
		$session_key = $ARCurrent->session->get('ARSessionKey', true);
		$cookie=ldGetCredentials();
		if ($session_key && $login==$cookie[$ARCurrent->session->id]['login']
			&& ($saved=$cookie[$ARCurrent->session->id]['check'])) {
			$check="{".md5($login.$session_key)."}";
			if ($check==$saved && !$ARCurrent->session->get('ARSessionTimedout', 1)) {
				$result=true;
				debug("login check ok");
			} else {
				debug("login check failed","all");
			}
		} else {
			debug("wrong login or corrupted cookie","all");
		}
		return $result;
	}
?>