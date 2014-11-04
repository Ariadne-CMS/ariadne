<?php
	class rcas_foreign extends rcas {

		function _login() {
			return $this->login();
		}

		function _logout() {
			return $this->logout();
		}

		function __construct($config) {
			if (!$config["redirect"]) {
				$config["redirect"] = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
			}
			if (!$config["url:login"]) {
				$this->error = "rcas_foreign: No login URL given!";
			}
			if (!$config["shared_secret"]) {
				$this->error = "rcas_foreign: No 'shared_secret' found in rcas_foreign configuration.";
			}

			if (!$config["ar:userPrefix"]) {
				$config["ar:userPrefix"] = "foreign_";
			}
			if (!$config["ar:userDir"]) {
				$config["ar:userDir"]   = "/system/users/";
			}
			if (!$config["ar:userProfile"]) {
				$config["ar:userProfile"]   = null;
			}
			if (!$config["ar:userType"]) {
				$config["ar:userType"] = "puser";
			}

			$this->config = $config;
		}

		function login() {
			$user = $this->getUser($this->config["ar:userPrefix"]);
			if (!$user) {
				if ($_GET["params"]) {
					ldStartSession();
					$userInfo	= $this->getUserInfo($_GET["params"]);
					if (!$userInfo['userID']) {
						$this->error = "Not logged on!";
					} else {
						$login		= $this->config["ar:userPrefix"].$userInfo["userID"];
						$user		= $this->setUser($login, $userInfo);
					}
				} else if (headers_sent()) {
					$this->error	= "rcas_foreign: could not authenticate user since headers are already sent.";
				} else {
					ldRedirect( $this->config["url:login"] );
					exit;
				}
			}
			return $user;
		}

		function logout($token) {
			// FIXME: implement logout
			return false;
		}

		function getUserInfo($params) {
			$params_notdec = $params;
			if (!$params_notdec) {
				die( 'No [params] given' );
			}
			$params_dec = @mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $this->config["shared_secret"], base64_decode($params_notdec), MCRYPT_MODE_CBC);
			if (!$params_dec) {
				die( 'Decryption failed!' );
			}

			$params = unserialize( $params_dec );
			if (!$params['userID'] || $params['timestamp'] < time() - 20) {
				die( 'Login failure!' );
			}

			return $params;
		}
	}
