<?php
	if ($this->CheckConfig()) {
		$hideSession = $AR->hideSessionIDfromURL;
		$AR->hideSessionIDfromURL = false;
		global $auth_config;

		$auth_class = "mod_auth_".$auth_config["method"];
		$mod_auth = new $auth_class($auth_config);
		$result = $mod_auth->checkLogin($username, $password, $path);
		if($result === true){
			$keyCheck = ldGenerateSessionKeyCheck();
			$arResult = $this->make_local_url().'?ARSessionKeyCheck='.RawURLEncode($keyCheck);
		} else {
			$arResult = $result;
		}
		$AR->hideSessionIDfromURL = $hideSession;
	} else {
		$arResult = false;
	}

