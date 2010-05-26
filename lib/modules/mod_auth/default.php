<?php
	class mod_auth_default {
		function mod_auth_default($config="", $requestedPath="") {
			global $store;

			$this->config = $config;
			if (!is_array($this->config['userdirs'])) {
				$this->config['userdirs'] = array();
			}
			array_unshift($this->config['userdirs'], "/system/users/"); // Make sure /system/users is always there on the first spot.

			if ($requestedPath && !$this->config['siteconfig']) {
				$site_config = current($store->call("auth.ini", "", $store->get($requestedPath)));
				$this->config['siteconfig'] = $site_config;
			}

			$this->config['userdirs'] = array_merge($this->config['userdirs'], (array) $this->config['siteconfig']['userdirs']);
		}

		function authExternalUser($login, $password) {
			return false;
		}

		function authUser($login, $password, $ARLoginPath="") {
		global $store, $AR;
			$criteria["object"]["implements"]["="]="'puser'";
			$criteria["login"]["value"]["="]="'".AddSlashes($login)."'";

			foreach ($this->config['userdirs'] as $userdir) {
				$user_ob = current(
					$store->call("system.get.phtml", "", $store->find($userdir, $criteria))
				);
				if ($user_ob) {
					if (!$ARLoginPath || ($ARLoginPath == $userdir)) {
						$user = $user_ob->call("system.authenticate.phtml", array("ARPassword" => $password));
						$ARUserDir = $userdir;
						break;
					}
				}
			}

			if (!$user) {
				$user = $this->authExternalUser($login, $password);
			}

			if ($user) {
				if ((!$user->data->config || !$user->data->config->disabled)) {
					if ($login !== "public") {
						/* welcome to Ariadne :) */
						ldSetCredentials($login, $ARUserDir);
					}
					$ARLogin = $user->data->login;
					$ARPassword = 0;
					$AR->user = $user;
					$result = true;
				} else {
					debug("getUser: user('$login') has been disabled", "all");
					$result = LD_ERR_ACCESS;
				}
			} else {
				debug("authUser: user('$login') could not authenticate", "all");
				$result = LD_ERR_ACCESS;
			}
			return $result;
		}

		function getUser($login, $ARUserDir="/system/users/") {
		global $store, $AR;

			$criteria["object"]["implements"]["="]="'puser'";
			$criteria["login"]["value"]["="]="'".AddSlashes($login)."'";

			$user = current(
				$store->call(
					"system.get.phtml",
					Array(),
					$store->find($ARUserDir, $criteria)
				)
			);

			if ($user) {
				if ((!$user->data->config || !$user->data->config->disabled)) {
					$AR->user = $user;
					$result = true;
				} else {
					debug("getUser: user('$login') has been disabled", "all");
					$result = LD_ERR_ACCESS;
				}

			} else {
				debug("getUser: user('$login') not found", "all");
				$result = LD_ERR_ACCESS;
			}
			return $result;
		}

		function checkLogin($login, $password, $requestedPath="") {
		global $ARCurrent, $AR;
			debug("checkLogin($login, [password])", "all");
			if ($login) {
				debug("checkLogin: initiating new login ($login)", "all");
				if ($ARCurrent->session) {
					$ARUserDir = $ARCurrent->session->get("ARUserDir", true);

					if (!$ARCurrent->session->get("ARLogin") ||
							$ARCurrent->session->get("ARLogin") == "public") {
						debug("checkLogin: logging into a public session (".$ARCurrent->session->id.")", "all");
						$result = $this->authUser($login, $password, $ARUserDir);
						if ($result !== true) {
							$this->getUser('public');
						}
					} else {
						if (ldCheckCredentials($login)) {
							debug("checkLogin: succesfully logged into private session (".$ARCurrent->session->id.")", "all");
							$result = $this->getUser($login, $ARUserDir);
						} else {
							if ($ARCurrent->session->get("ARLogin") == $login) {
								debug("checkLogin: user ($login) tries to login to his session without a cookie set", "all");
								$result = $this->authUser($login, $password, $ARUserDir);
								if ($result !== true) {
									$this->getUser('public');
								}
							} else
							if (ldCheckCredentials($ARCurrent->session->get("ARLogin")))  {
								debug("checkLogin: user tries to login as another user", "all");
								$result = $this->authUser($login, $password, $ARUserDir);
								if ($result !== true) {
									$this->getUser('public');
								}
							} else {
								debug("checkLogin: could not login to private session (".$ARCurrent->session->id."): creating a new one", "all");
								ldStartSession();
								$result = $this->authUser($login, $password, $ARUserDir);
								if ($result !== true) {
									$this->getUser('public');
								}
							}
						}
					}
				} else {
					debug("checkLogin: trying to log on", "all");
					$result = $this->authUser($login, $password, $ARUserDir);
					if ($result !== true) {
						$this->getUser('public');
					}

				}
			} else {
				if ($ARCurrent->session) {
					$ARUserDir = $ARCurrent->session->get("ARUserDir", true);
					if (!$ARCurrent->session->get("ARLogin")) {
						if ($ARCurrent->session->get("ARSessionTimedout", 1)) {
							$ARCurrent->session->put("ARSessionTimedout", 0, 1);
						}
						debug("checkLogin: logging in with public session (".$ARCurrent->session->id.")", "all");
						$result = $this->checkLogin("public", "none");
					} else
					if ($ARCurrent->session->get("ARSessionTimedout", 1)) {
						debug("checkLogin: session has been timedout, forcing login", "all");
						// become public
						$this->getUser('public');
						$result = LD_ERR_SESSION;
					} else {
						$cookie = ldGetCredentials();
						$cookie_login = $cookie[$ARCurrent->session->id]['login'];
		
						if ($cookie_login) {
							$login = $ARCurrent->session->get("ARLogin");
							if (ldCheckCredentials($login)) {
								debug("checkLogin: logging ($login) into a private session (".$ARCurrent->session->id.") with credentials from cookie", "all");
								$result = $this->getUser($login, $ARUserDir);
							} else {
								debug("checkLogin: could not login ($login) on private session (".$ARCurrent->session->id.") with credentials from cookie: removing cookie", "all");
								unset($cookie[$ARCurrent->session->id]);
								setcookie("ARCookie", serialize($cookie), 0, '/');
								$this->getUser('public');
								$result = LD_ERR_ACCESS;
							}
						} else {
							debug("checkLogin: user tried to hijack a session (".$ARCurrent->session->id.") ", "all");
							$this->getUser('public');
							$result = LD_ERR_ACCESS;
						}
					}
				} else {
					if ($AR->arSessionRespawn) {
						debug("checkLogin: trying to respawn a session", "all");
						$cookie = ldGetCredentials();
						if (is_array($cookie)) {
							reset($cookie);
							while (!$result && (list($sid, $sval)=each($cookie))) {
								ldStartSession($sid);
								$login = $ARCurrent->session->get("ARLogin");
								debug("checkLogin: trying to respawn session ($sid) for user ($login)", "all");
								if (ldCheckCredentials($login)) {
									$ARUserDir = $ARCurrent->session->get("ARUserDir", true);
									debug("checkLogin: credentials matched, loading user", "all");
									$result = $this->getUser($login, $ARUserDir);
								} else {
									debug("checkLogin: credentials didn't match", "all");
								}
							}
						}
					}
					if (!$result) {
						debug("checkLogin: normal public login", "all");
						$result = $this->authUser("public", "none");
					}
				}
			}
			return $result;
		}
	}
?>