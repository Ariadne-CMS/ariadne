<?php

	function ldIsUserCookieName($name) {
		if (!is_string($name) || $name === '' || str_starts_with($name, 'ARSessionCookie')) {
			return false;
		}
		foreach (array('ARCurrentSession', 'ARCookie') as $protected) {
			if ($name === $protected || str_starts_with($name, $protected.'[')) {
				return false;
			}
		}
		return true;
	}

	function ldSetCredentials($login, $ARUserDir="/system/users/") {
		global $ARCurrent, $AR, $LD_NO_SESSION_SUPPORT;

		if ($LD_NO_SESSION_SUPPORT) {
			debug("ldSetCredentials($login): no session support");
			return;
		}

		if (!$ARUserDir || $ARUserDir == "") {
			$ARUserDir = "/system/users/";
		}

		// Make sure the login is lower case. Because of the
		// numerous checks on "admin".
		$login = strtolower( $login );

		debug("ldSetCredentials($login)","object");

		if (!$ARCurrent->session) {
			ldStartSession();
		} else {
			/* use the same sessionid if the user didn't login before */
			ldStartSession($ARCurrent->session->id);
		}
		$ARCurrent->session->put("ARLogin", $login);
		$ARCurrent->session->put("ARUserDir", $ARUserDir, true);

		/* create the session key */
		$session_key = bin2hex(random_bytes(16));

		$ARCurrent->session->put("ARSessionKey", $session_key, true);
		$ARCurrent->session->put("ARSessionTimedout", 0, 1);

		/* now save our session */
		$ARCurrent->session->save();

		$cookies = ldGetCredentials();

		foreach($cookies as $sessionid => $cookie){
			if(!$AR->hideSessionIDfromURL){
				if (!$ARCurrent->session->sessionstore->exists("/$sessionid/")) {
					$data = ldDecodeCookie($cookie);
					if(is_array($data) && is_int($data['timestamp'] ?? null)) {
						// don't just kill it, it may be from another ariadne installation
						if ($data['timestamp']<(time()-86400)) {
							// but do kill it if it's older than one day
							unset($cookies[$sessionid]);
							setcookie("ARSessionCookie[".$sessionid."]",false);
						}
					}
				}
			} else {
				// only 1 cookie allowed, unset all cookies
				if( $sessionid != $ARCurrent->session->id) {
					setcookie("ARSessionCookie[".$sessionid."]",null);
				}
			}
		}

		$data = array();

		$data['login']=$login;
		$data['timestamp']=time();
		$data['check']=ldGenerateSessionKeyCheck();

		$cookie=ldEncodeCookie($data);
		$cookiename = "ARSessionCookie[".$ARCurrent->session->id."]";

		debug("setting cookie ()($cookie)");
		header('P3P: CP="NOI CUR OUR"');
		$https = (($_SERVER['HTTPS'] ?? null)=='on');
		setcookie($cookiename,$cookie, 0, '/', false, $https, true);
	}

	function ldGenerateSessionKeyCheck() {
	global $ARCurrent;
		$session_key = $ARCurrent->session->get('ARSessionKey', true);
		$ARUserDir   = $ARCurrent->session->get('ARUserDir', true);
		$login       = $ARCurrent->session->get('ARLogin');
		return "{".md5($login.$ARUserDir.$session_key)."}";
	}

	function ldGetCredentials() {
		debug("ldGetCredentials()","object");
		$ARSessionCookie = $_COOKIE["ARSessionCookie"] ?? null;
		return is_array($ARSessionCookie) ? $ARSessionCookie : array();
	}

	function ldCheckCredentials($login) {
	global $ARCurrent, $AR;
		debug("ldCheckCredentials()","object");
		$result=false;
		$cookie=ldGetCredentials();
		$data = ldDecodeCookie($cookie[$ARCurrent->session->id] ?? null);
		if ($login === ($data['login'] ?? null)
			&& ($saved=($data['check'] ?? null))) {
			$check=ldGenerateSessionKeyCheck();
			if ($check === $saved && !$ARCurrent->session->get('ARSessionTimedout', 1)) {
				$result=true;
			} else {
				debug("login check failed","all");
			}
		} else {
			$ARSessionKeyCheck = $_GET['ARSessionKeyCheck'] ?? null;
			if (!$ARSessionKeyCheck) {
				$ARSessionKeyCheck = $_POST['ARSessionKeyCheck'] ?? null;
			}
			if ($ARSessionKeyCheck) {
				debug("ldCheckCredentials: trying ARSessionKeyCheck ($ARSessionKeyCheck)");
				if ($ARSessionKeyCheck == ldGenerateSessionKeyCheck()) {
					$result = true;
				}
			} else {
				debug("wrong login or corrupted cookie","all");
			}
		}
		return $result;
	}

	function ldDecodeCookie($cookie) {
		global $AR;
		if (!is_string($cookie) || $cookie === '') {
			return null;
		}
		$data = json_decode($cookie,true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			if(isset($AR->sessionCryptoKey)) {
				$key = base64_decode($AR->sessionCryptoKey);
				$crypto = new ar_crypt($key,MCRYPT_RIJNDAEL_256,1);
				$decoded = $crypto->decrypt($cookie);
				$data = is_string($decoded) ? json_decode($decoded,true) : null;
			}
		}

		return is_array($data) ? $data : null;
	}

	function ldEncodeCookie($cookie) {
		global $AR;
		$data = json_encode($cookie);
		if(isset($AR->sessionCryptoKey)) {
			$key = base64_decode($AR->sessionCryptoKey);
			$crypto = new ar_crypt($key,MCRYPT_RIJNDAEL_256,1);
			$encdata = $crypto->crypt($data);
			if($encdata !== false) {
				$data = $encdata;
			}
		}
		return $data;
	}
