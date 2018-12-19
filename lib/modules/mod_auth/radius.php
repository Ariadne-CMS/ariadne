<?php

	include_once($ariadne."/modules/mod_auth/default.php");

	class mod_auth_radius extends mod_auth_default {

		function __construct($config) {
			$this->config = $config;
		}

		function storeExternalUser($login, $userData) {
		global $AR, $store;
			// become admin for the moment
			$aLogin		= $this->config["import_user_by"];
			$AR->user	= current($store->call("system.get.phtml", "", $store->find("/system/users/", "login.value='$aLogin' and object.implements='puser'")));

			$user = $this->getUser($login);
			if ($user !== true) {
				$user_dir		= $this->config["import_user_directory"];
				$user_profile		= $this->config["import_user_profile"];
				$user_type		= $this->config["import_user_type"];
				if (!$user_type) {
					$user_type = "puser";
				}

				debug("ldAuthRadius: user ($login) didn't exist before: creating", "all");
				$data = $userData;
				$data["arNewFilename"] = "$user_dir$login/";
				$data["profile"] = $user_profile;
				$data["setowner"] = true;

				$user = $store->newobject(
							"$user_dir$login/",
							"$user_dir",
							"$user_type",
							new baseObject);

				$user->arIsNewObject = true;
				$user->call('system.save.data.phtml', $data);

				return $user;
			} else {
				// $AR->user was set by getUser and contains the correct user now.
				return $AR->user;
			}
	 	}

	 	function authExternalUser($login, $password) {

			$res = radius_auth_open();
			if (!radius_add_server($res, $this->config['radius_server'], $this->config['radius_port'], $this->config['sharedsecret'], 3, 3)) {
				debug('RadiusError:' . radius_strerror($res). "\n",'auth');
				return false;
			}

			if (!radius_create_request($res, RADIUS_ACCESS_REQUEST)) {
				debug('RadiusError:' . radius_strerror($res). "\n",'auth');
				return false;
			}

			if (!radius_put_string($res, RADIUS_NAS_IDENTIFIER, isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'))  {
				debug('RadiusError:' . radius_strerror($res). "\n",'auth');
				return false;
			}

			if (!radius_put_int($res, RADIUS_SERVICE_TYPE, RADIUS_FRAMED)) {
				debug('RadiusError:' . radius_strerror($res). "\n",'auth');
				return false;
			}

			if (!radius_put_int($res, RADIUS_FRAMED_PROTOCOL, RADIUS_PPP)) {
				debug('RadiusError:' . radius_strerror($res). "\n",'auth');
				return false;
			}

			if (!radius_put_string($res, RADIUS_CALLING_STATION_ID, isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : '127.0.0.1') == -1) {
				debug('RadiusError:' . radius_strerror($res). "\n",'auth');
				return false;
			}

			if (!radius_put_string($res, RADIUS_USER_NAME, $login)) {
				debug('RadiusError:' . radius_strerror($res). "\n",'auth');
				return false;
			}
			if($password){
				if (!radius_put_string($res, RADIUS_USER_PASSWORD, $password)) {
					debug('RadiusError:' . radius_strerror($res). "\n",'auth');
					return false;
				}
			}

			if (!radius_put_int($res, RADIUS_SERVICE_TYPE, RADIUS_FRAMED)) {
				debug('RadiusError:' . radius_strerror($res). "\n",'auth');
				return false;
			}

			if (!radius_put_int($res, RADIUS_FRAMED_PROTOCOL, RADIUS_PPP)) {
				debug('RadiusError:' . radius_strerror($res). "\n",'auth');
				return false;
			}

			$req = radius_send_request($res);
			if (!$req) {
				debug('RadiusError:' . radius_strerror($res). "\n",'auth');
				return false;
			}

			$user = false;
			switch($req) {
				case RADIUS_ACCESS_ACCEPT:
					$userData = Array();
					$userData["name"] = $login;
					$userData["newpass1"] = '!';
					$userData["newpass2"] = '!';
	 				$user = $this->storeExternalUser($login, $userData);
					break;

				case RADIUS_ACCESS_REJECT:
					debug("RadiusError: Radius Request rejected\n",'auth');
					break;

				default:
					debug("RadiusError: Unknown answer\n",'auth');
			}
			return $user;
	 	}
	}
