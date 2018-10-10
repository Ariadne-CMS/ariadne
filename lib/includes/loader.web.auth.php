<?php

	function ldGetCurrentTemplate( $function ) {
		if ( isset($function) ) {
			return $function;
		} else {
			$me = ar_ariadneContext::getObject();
			if ($me) {
				$context = $me->getContext();
				return $context['arCallFunction'];
			}
		}
		return null;
	}

	function ldSetCredentials($login, $ARUserDir="/system/users/") {
		global $ARCurrent, $AR;
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

		$cookies = (array)$_COOKIE["ARSessionCookie"];
		$https = ($_SERVER['HTTPS']=='on');

		$currentCookies = array();

		foreach($cookies as $sessionid => $cookie){
			if(!$AR->hideSessionIDfromURL){
				if (!$ARCurrent->session->sessionstore->exists("/$sessionid/")) {
					$data = ldDecodeCookie($cookie);
					if(is_array($data)) {
						// don't just kill it, it may be from another ariadne installation
						if ($data['timestamp']<(time()-86400)) {
							// but do kill it if it's older than one day
							unset($cookies[$sessionid]);
							setcookie("ARSessionCookie[".$sessionid."]",false);
						} else {
							$currentCookies[$sessionid] = $data['timestamp'];
						}
					}
				}
			}
		}

		// Keep a maximum of 15 session cookies for one client
		if (sizeof($currentCookies) > 15) {
			sort($currentCookies);
			$removed = array_slice(array_keys($input), 15); // grab the session ids for all the older sessions
			foreach ($removed as $sessionid) {
				// and kill those sessions
				unset($cookies[$sessionid]);
				setcookie("ARSessionCookie[".$sessionid."]",false);
			}
		}

		if( $ARCurrent->session->id !== 0) {
			$data = array();

			$data['login']=$login;
			$data['timestamp']=time();
			$data['check']=ldGenerateSessionKeyCheck();

			$cookie=ldEncodeCookie($data);
			$cookiename = "ARSessionCookie[".$ARCurrent->session->id."]";

			header('P3P: CP="NOI CUR OUR"');
			setcookie('ARCurrentSession', $ARCurrent->session->id, 0, '/', false, $https, true);
			setcookie($cookiename,$cookie, 0, '/', false, $https, true);
		}
	}

	function ldAccessTimeout($path, $message, $args = null, $function = null) {
	global $ARCurrent, $store;
		/*
			since there is no 'peek' function, we need to pop and push
			the arCallArgs variable.
		*/

		if( isset( $args ) ) {
			$arCallArgs = $args;
		} else {
			$arCallArgs = @array_pop($ARCurrent->arCallStack);
			@array_push($ARCurrent->arCallStack, $arCallArgs);
		}

		$eventData = new baseObject();
		$eventData->arCallPath = $path;
		$eventData->arCallFunction = ldGetCurrentTemplate( $function );
		$eventData->arCallArgs = $arCallArgs;
		$eventData->arLoginMessage = $message;
		$eventData->arReason = 'access timeout';
		$eventData = ar_events::fire( 'onaccessdenied', $eventData );
		if ( $eventData ) {

			$arCallArgs = $eventData->arCallArgs;
			$arCallArgs["arLoginMessage"] = $eventData->message;

			if (!$ARCurrent->arLoginSilent) {
				$ARCurrent->arLoginSilent = true;
				$store->call("user.session.timeout.html",
					$arCallArgs,
					$store->get($path) );
			}

		}
	}

	function ldAccessDenied($path, $message, $args = null, $function = null) {
	global $ARCurrent, $store;
		/*
			since there is no 'peek' function, we need to pop and push
			the arCallArgs variable.
		*/

		if( isset( $args ) ) {
			$arCallArgs = $args;
		} else {
			$arCallArgs = @array_pop($ARCurrent->arCallStack);
			@array_push($ARCurrent->arCallStack, $arCallArgs);
		}

		$eventData = new baseObject();
		$eventData->arCallPath = $path;
		$eventData->arCallFunction = ldGetCurrentTemplate( $function );
		$eventData->arCallArgs = $arCallArgs;
		$eventData->arLoginMessage = $message;
		$eventData->arReason = 'access denied';

		$eventData = ar_events::fire( 'onaccessdenied', $eventData );

		if ( $eventData ) {

			$arCallArgs = $eventData->arCallArgs;
			$arCallArgs["arLoginMessage"] = $eventData->message;

			if (!$ARCurrent->arLoginSilent) {
				$ARCurrent->arLoginSilent = true;
				$store->call("user.login.html",
					$arCallArgs,
					$store->get($path) );
			}
		}
	}

	function ldAccessPasswordExpired($path, $message, $args=null, $function = null) {
	global $ARCurrent, $store;
		/*
			since there is no 'peek' function, we need to pop and push
			the arCallArgs variable.
		*/

		if( isset( $args ) ) {
			$arCallArgs = $args;
		} else {
			$arCallArgs = @array_pop($ARCurrent->arCallStack);
			@array_push($ARCurrent->arCallStack, $arCallArgs);
		}

		$eventData = new baseObject();
		$eventData->arCallPath = $path;
		$eventData->arCallFunction = ldGetCurrentTemplate( $function );
		$eventData->arLoginMessage = $message;
		$eventData->arReason = 'password expired';
		$eventData->arCallArgs = $arCallArgs;
		$eventData = ar_events::fire( 'onaccessdenied', $eventData );
		if ( $eventData ) {

			$arCallArgs = $eventData->arCallArgs;
			$arCallArgs["arLoginMessage"] = $eventData->arLoginMessage;

			if (!$ARCurrent->arLoginSilent) {
				$ARCurrent->arLoginSilent = true;
				$store->call("user.password.expired.html",
					$arCallArgs,
					$store->get($path) );
			}
		}

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
		$ARSessionCookie = $_COOKIE["ARSessionCookie"];
		return $ARSessionCookie;
	}

	function ldCheckCredentials($login) {
	global $ARCurrent, $AR;
		debug("ldCheckCredentials($login)","object");
		$result=false;
		$cookie=ldGetCredentials();
		$data = ldDecodeCookie($cookie[$ARCurrent->session->id]);
		if ($login === $data['login']
			&& ($saved=$data['check'])) {
			$check=ldGenerateSessionKeyCheck();
			if ($check === $saved && !$ARCurrent->session->get('ARSessionTimedout', 1)) {
				$result=true;
			} else {
				debug("login check failed","all");
			}
		} else {
			$ARSessionKeyCheck = $_GET['ARSessionKeyCheck'];
			if (!$ARSessionKeyCheck) {
				$ARSessionKeyCheck = $_POST['ARSessionKeyCheck'];
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
		$data = json_decode($cookie,true);
		if(is_null($data)){
			if(isset($AR->sessionCryptoKey) && function_exists('mcrypt_encrypt') ) {
				$key = base64_decode($AR->sessionCryptoKey);
				$crypto = new ar_crypt($key,MCRYPT_RIJNDAEL_256,1);
				$data = json_decode($crypto->decrypt($cookie),true);
			}
		}

		return $data;
	}

	function ldEncodeCookie($cookie) {
		global $AR;
		$data = json_encode($cookie);
		if(isset($AR->sessionCryptoKey) && function_exists('mcrypt_encrypt') ) {
			$key = base64_decode($AR->sessionCryptoKey);
			$crypto = new ar_crypt($key,MCRYPT_RIJNDAEL_256,1);
			$encdata = $crypto->crypt($data);
			if($encdata !== false) {
				$data = $encdata;
			}
		}
		return $data;
	}
