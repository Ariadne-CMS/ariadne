<?php
	class rcas {

		function getUser($prefix) {
		global $AR;
			if (substr($AR->user->data->login, 0, strlen($prefix)) == $prefix) {
				return $AR->user;
			} else {
			}
		}

	 	function setUser($login, $userInfo = Array()) {
		global $AR, $store;
			pobject::pushContext(Array("scope" => "php"));
			$result = mod_auth_default::getUser($login, $this->config["ar:userDir"]);
			if ($result === LD_ERR_ACCESS) {
				$aLogin     = 'admin'; // FIXME: make this configurable

				$AR->user   = current($store->call("system.get.phtml", "", $store->find("/system/users/", "login.value='$aLogin' and object.implements='puser'")));

				$user_dir   = $this->config["ar:userDir"];
				$user_profile = $this->config["ar:userProfile"];

				$data = Array();
				$data["arNewFilename"] = "$user_dir$login/";

				$data["name"] = $login;
				$data["newpass1"] = '!';
				$data["newpass2"] = '!';
				$data["profile"] = $user_profile;
				$data["setowner"] = true;
				$data["email"] = $userInfo["email"];

				foreach ($userInfo as $key => $value) {
					$data["custom"]["none"][$key] = $value;
				}

				$userType = $this->config["ar:userType"] ? $this->config["ar:userType"] : "puser";

				$user = $store->newobject(
							"$user_dir$login/",
							"$user_dir",
							$userType,
							new baseObject);

				$user->arIsNewObject = true;
				$user->call('system.save.data.phtml', $data);
				$AR->user = $user;
			}
			ldSetCredentials($login, $this->config["ar:userDir"]);
			// unbecome system user
			pobject::popContext();
			return $AR->user;
		}

	}

	class pinp_rcas {

		function _init($module, $config = Array()) {
			global $ariadne;
			require_once($ariadne.'/modules/mod_rcas/'.basename($module).".php");
			$className = "rcas_$module";
			return new $className($config);
		}

	}
