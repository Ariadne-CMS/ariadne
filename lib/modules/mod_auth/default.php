<?php
	class mod_auth_default {

		function __construct($config=Array()) {
			$this->config = $config;
		}

		function authExternalUser($login, $password, $requestedPath = "/") {
			return false;
		}

		function loadConfig($requestedPath = "/") {
		global $ARConfig, $store;
			if (!$requestedPath) {
				$requestedPath = "/";
			}
			$_cache = $ARConfig->cache;
			while ( $requestedPath && $requestedPath!='/' && !$store->exists($requestedPath) ) {
				$requestedPath = $store->make_path( $requestedPath, '..' );
			}
			$site = current($store->call("system.get.phtml", "", $store->get($requestedPath)));
			if ($site) {
				$site_config = $site->loadUserConfig();
				$this->config['siteConfig'] = $site_config['authentication'];
			}
			$ARConfig->cache = $_cache;
			return $this->config['siteConfig'];
		}

		function authUser($login, $password, $ARLoginPath="") {
		global $store, $AR;
			// Make sure we always have a user.
			$this->getUser('public');

			$criteria = array();
			$criteria["object"]["implements"]["="]="puser";
			$criteria["login"]["value"]["="]=$login;

			$siteConfig = $this->loadConfig($ARLoginPath);
			foreach ((array)$siteConfig['userdirs'] as $userdir) {

				$user = current($store->call("system.authenticate.phtml", array("ARPassword" => $password),
						$store->find($userdir, $criteria, 1, 0)));
				if ($user) {
					$ARUserDir = $userdir;
					break;
				}
			}

			if (!$user) {
				$user = $this->authExternalUser($login, $password, $ARLoginPath);
				$ARUserDir = $user->parent;
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
			if (!$ARUserDir) {
				$ARUserDir = "/system/users/";
			}

			$criteria = array();
			$criteria["object"]["implements"]["="]="puser";
			$criteria["login"]["value"]["="]=$login;

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

		function checkLogin($login, $password, $requestedPath="/") {
		global $ARCurrent, $AR;
			debug("checkLogin($login, [password])", "all");
			$result = null;
			if ($login) {
				debug("checkLogin: initiating new login ($login)", "all");
				if ($ARCurrent->session) {
					$ARUserDir = $ARCurrent->session->get("ARUserDir", true);
					if (!$ARUserDir) {
						$ARUserDir = "/system/users/";
					}

					if (!$ARCurrent->session->get("ARLogin") ||
							$ARCurrent->session->get("ARLogin") == "public") {
						debug("checkLogin: logging into a public session (".$ARCurrent->session->id.")", "all");
						$result = $this->authUser($login, $password, $requestedPath);
						if ($result !== true) {
							$this->getUser('public');
						}
					} else {
						if (ldCheckCredentials($login)) {
							debug("checkLogin: succesfully logged into private session (".$ARCurrent->session->id.")", "all");
							$result = $this->getUser($login, $ARUserDir);
						} else {
							if ($ARCurrent->session->get("ARLogin") == $login) {
								debug("checkLogin: user ($login) tries to login to his session without a cookie set @ $ARUserDir", "all");
								$result = $this->authUser($login, $password, $ARUserDir);
								if ($result !== true) {
									$this->getUser('public');
								}
							} else
							if (ldCheckCredentials($ARCurrent->session->get("ARLogin")))  {
								debug("checkLogin: user tries to login as another user", "all");
								$result = $this->authUser($login, $password, $requestedPath);
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
					$result = $this->authUser($login, $password, $requestedPath);
					if ($result !== true) {
						$this->getUser('public');
					}

				}
			} else {
				if ($ARCurrent->session) {
					$ARUserDir = $ARCurrent->session->get("ARUserDir", true);
					if (!$ARUserDir) {
						$ARUserDir = "/system/users/";
					}

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
						$login = $ARCurrent->session->get("ARLogin");
						if (ldCheckCredentials($login)) {
							debug("checkLogin: logging ($login) into a private session (".$ARCurrent->session->id.") with credentials from cookie", "all");
							$result = $this->getUser($login, $ARUserDir);
						} else {
							debug("checkLogin: could not login ($login) on private session (".$ARCurrent->session->id.") with credentials from cookie: removing cookie", "all");
							// FIXME: only the loader should know about cookies for sessions
							setcookie("ARSessionCookie[".$ARCurrent->session->id."]", false);
							$this->getUser('public');
							$result = LD_ERR_ACCESS;
						}
					}
				} else {
					if ($AR->arSessionRespawn) {
						debug("checkLogin: trying to respawn a session", "all");
						$cookies = ldGetCredentials();
						if (is_array($cookies)) {
							reset($cookies);
							while (!$result && (list($sid, $sval)=each($cookies))) {
								ldStartSession($sid);
								$login = $ARCurrent->session->get("ARLogin");
								debug("checkLogin: trying to respawn session ($sid) for user ($login)", "all");
								if (ldCheckCredentials($login)) {
									$ARUserDir = $ARCurrent->session->get("ARUserDir", true);
									if (!$ARUserDir) {
										$ARUserDir = "/system/users/";
									}

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
