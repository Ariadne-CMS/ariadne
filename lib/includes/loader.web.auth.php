<?php

	function ldSetCredentials($login) {
	global $ARCurrent, $AR, $HTTP_COOKIE_VARS;

		// Make sure the login is lower case. Because of the
		// numerous checks on "admin".
		$login = strtolower( $login );

		$ARCookie = stripslashes($HTTP_COOKIE_VARS["ARCookie"]);

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
		$session_key = ARCrypt(uniqid(rand(), true));

		$ARCurrent->session->put("ARSessionKey", $session_key, true);
		$ARCurrent->session->put("ARSessionTimedout", 0, 1);

		/* now save our session */
		$ARCurrent->session->save();

		if (!$AR->hideSessionIDfromURL) {
			$cookie=unserialize($ARCookie);
		} else {
			// If we are hiding the session id from the URL,
			// there can only be one user per cookie, so we
			// throw the old stuff away
			$cookie=array();
		}

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

		$cookie[$ARCurrent->session->id]['login']=$login;
		$cookie[$ARCurrent->session->id]['timestamp']=time();
		$cookie[$ARCurrent->session->id]['check']="{".ARCrypt($login.$session_key)."}";
		$ARCookie=serialize($cookie);
		debug("setting cookie ($ARCookie)");
		setcookie("ARCookie",$ARCookie, 0, '/');
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

	function ldCheckCredentials($login) {
	global $ARCurrent, $AR;
		debug("ldCheckCredentials()","object");
		$result=false;
		$session_key = $ARCurrent->session->get('ARSessionKey', true);
		$cookie=ldGetCredentials();
		if ($session_key && $login==$cookie[$ARCurrent->session->id]['login']
			&& ($saved=$cookie[$ARCurrent->session->id]['check'])) {
			$check="{".ARCrypt($login.$session_key)."}";
			if ($check==$saved && !$ARCurrent->session->get('ARSessionTimedout', 1)) {
				$result=true;
			} else {
				debug("login check failed","all");
			}
		} else {
			debug("wrong login or corrupted cookie","all");
		}			
		return $result;
	}
?>
