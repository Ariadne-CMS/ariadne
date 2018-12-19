<?php
	// dl depends on your path settings
	dl("smbauth.so");

	include_once($ariadne."/modules/mod_auth/default.php");

	class mod_auth_smb extends mod_auth_default {

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

				debug("ldAuthSMB: user ($login) didn't exist before: creating", "all");
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
			if ($password) {
				// host, domain, group, user, password
				$result = validate($this->config["smb_server"], $this->config["smb_server_domain"], $this->config["smb_server_group"], $login, $password);
				if ($result != 0) {
					debug("ldAuthSMB: could not connect or authenticate to the SMB server [".$this->config["smb_server"]."] for user '$login'", "all");
				} else {

					$userData = Array();
					$userData["name"] = $login;
					$userData["newpass1"] = '!';
					$userData["newpass2"] = '!';
					$user = $this->storeExternalUser($login, $userData);
				}
			}
			return $user;
		}

	}
