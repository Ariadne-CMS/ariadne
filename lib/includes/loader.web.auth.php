<?php

	function ldAuthUser($login, $password) {
	global $ARLogin, $ARPassword, $store, $AR;
		$criteria["object"]["implements"]["="]="'puser'";
		$criteria["login"]["value"]["="]="'".AddSlashes($login)."'";
		$user = current(
					$store->call(
						"system.authenticate.phtml",
						Array(
							"ARPassword" => $password
						),
						$store->find("/system/users/", $criteria)
					));
		if ($user) {
			if ($login !== "public") {
				/* welcome to Ariadne :) */
				ldSetCredentials($login);
			}
			$ARLogin = $user->data->login;
			$ARPassword = 0;
			$AR->user = $user;
			$result = true;
		} else {
			debug("ldAuthUser: user('$user') could not authenticate", "all");
			$result = LD_ERR_ACCESS;
		}
		return $result;
	}

	function ldGetUser($login) {
	global $ARLogin, $store, $AR;
		$criteria["object"]["implements"]["="]="'puser'";
		$criteria["login"]["value"]["="]="'".AddSlashes($login)."'";
		$user = current(
					$store->call(
						"system.get.phtml",
						Array(),
						$store->find("/system/users/", $criteria)
					));
		if ($user) {
			$ARLogin = $user->data->login;
			$AR->user = $user;
			$result = true;
		} else {
			debug("ldGetUser: user('$user') not found", "all");
			$result = LD_ERR_ACCESS;
		}
	}

	function ldCheckLogin($login, $password) {
	global $ARCurrent, $AR;
		debug("ldCheckLogin($login, [password])", "all");
		if ($login) {
			debug("ldCheckLogin: initiating new login ($login)", "all");
			if ($ARCurrent->session) {
				if (!$ARCurrent->session->get("ARLogin") ||
						$ARCurrent->session->get("ARLogin") == "public") {
					debug("ldCheckLogin: logging into a public session (".$ARCurrent->session->id.")", "all");
					$result = ldAuthUser($login, $password);
				} else {
					if (ldCheckCredentials($login)) {
						debug("ldCheckLogin: succesfully logged into private session (".$ARCurrent->session->id.")", "all");
						$result = ldGetUser($login);
					} else {
						if ($ARCurrent->session->get("ARLogin") == $login) {
							debug("ldCheckLogin: user ($login) tries to login to his session without a cookie set", "all");
							$result = ldAuthUser($login, $password);
						} else
						if (ldCheckCredentials($ARCurrent->session->get("ARLogin")))  {
							debug("ldCheckLogin: user tries to login as another user", "all");
							$result = ldAuthUser($login, $password);
						} else {
							debug("ldCheckLogin: could not login to private session (".$ARCurrent->session->id."): creating a new one", "all");
							ldStartSession();
							$result = ldAuthUser($login, $password);
						}
					}
				}
			} else {
				debug("ldCheckLogin: starting new session", "all");
				ldStartSession();
				$result = ldAuthUser($login, $password);
			}
		} else {
			if ($ARCurrent->session) {
				if (!$ARCurrent->session->get("ARLogin")) {
					if ($ARCurrent->session->get("ARSessionTimedout", 1)) {
						$ARCurrent->session->put("ARSessionTimedout", 0, 1);
					}
					debug("ldCheckLogin: logging in with public session (".$ARCurrent->session->id.")", "all");
					$result = ldCheckLogin("public", "none");
				} else
				if ($ARCurrent->session->get("ARSessionTimedout", 1)) {
					debug("ldCheckLogin: session has been timedout, forcing login", "all");
					$result = LD_ERR_SESSION;
				} else {
					$cookie = ldGetCredentials();
					$cookie_login = $cookie[$ARCurrent->session->id]['login'];
					if ($cookie_login) {
						$login = $ARCurrent->session->get("ARLogin");
						if (ldCheckCredentials($login)) {
							debug("ldCheckLogin: logging ($login) into a private session (".$ARCurrent->session->id.") with credentials from cookie", "all");
							$result = ldGetUser($login);
						} else {
							debug("ldCheckLogin: could not login ($login) on private session (".$ARCurrent->session->id.") with credentials from cookie: removing cookie", "all");
							unset($cookie[$ARCurrent->session->id]);
							setcookie("ARCookie", serialize($cookie), 0, '/');
							$result = LD_ERR_ACCESS;
						}
					} else {
						debug("ldCheckLogin: user tried to hijack a session (".$ARCurrent->session->id.") ", "all");
						$result = LD_ERR_ACCESS;
					}
				}
			} else {
				if ($AR->arSessionRespawn) {
					debug("ldCheckLogin: trying to respawn a session", "all");
					$cookie = ldGetCredentials();
					if (is_array($cookie)) {
						reset($cookie);
						while (!$result && (list($sid, $sval)=each($cookie))) {
							ldStartSession($sid);
							$login = $ARCurrent->session->get("ARLogin");
							debug("ldCheckLogin: trying to respawn session ($sid) for user ($login)", "all");
							if (ldCheckCredentials($login)) {
								debug("ldCheckLogin: credentials matched, loading user", "all");
								$result = ldGetUser($login);
							} else {
								debug("ldCheckLogin: credentials didn't match", "all");
							}
						}
					}
				} else {
					debug("ldCheckLogin: normal public login", "all");
					$result = ldAuthUser("public", "none");
				}
			}
		}
		return $result;
	}

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
		if ($login==$cookie[$ARCurrent->session->id]['login']
			&& ($saved=$cookie[$ARCurrent->session->id]['check'])) {
			$check="{".ARCrypt($login.$session_key)."}";
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
?>