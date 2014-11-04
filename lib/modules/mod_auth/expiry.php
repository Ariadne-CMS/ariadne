<?php
	/*
		mod_auth/expiry.php

		Password expiry module to extend the default Ariadne
		password authentication.

		This module provides a modified checkLogin function that
		returns LD_ERR_EXPIRED if the password given is correct, but
		expired. The handling of this return value is done within
		loader.php

		To have a password that never expires, unset
		$user->data->password_expiry; This also means that after
		adding this module, for normal users the passwords will not
		expire, they have to be specifically set to expire.

		To activate this module, configure it in lib/configs/authentication.phtml;
		Sample configuration is:

		------ authentication.phtml ------
		$auth_config["method"] = "expiry";
		$auth_config["expiry"] = 15552000;
		----------------------------------

		By default the expiry time is 180 days.
	*/
        require_once 'default.php';

	class mod_auth_expiry extends mod_auth_default {
		function checkLogin($login, $password) {
			global $AR;
			$result = parent::checkLogin($login, $password);
			if ($result === true && $login) { // Only expire passwords on a new session.
				$login = $AR->user->data->login;
				$password_expiry_date = $AR->user->data->password_expiry;

				// If the password expiry is not set, the password never expires. (Default behaviour).
				if (isset($password_expiry_date) && ($login != 'public')) {
					if ($password_expiry_date < time()) {
						// Password expired;
						$result = LD_ERR_EXPIRED;
					}
				}
			}
			return $result;
		}
	}
