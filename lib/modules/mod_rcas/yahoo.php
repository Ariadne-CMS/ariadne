<?php

	class rcas_yahoo extends rcas {

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
				$config["url:login"]	= "http://api.screenname.aol.com/auth/login";
			}
 			if (!$config["url:logout"]) {
				$config["url:logout"]	= "http://api.screenname.aol.com/auth/logout";
			}
			if (!$config["url:info"]) {
				$config["url:info"]		= "http://api.screenname.aol.com/auth/getInfo";
			}
			if (!$config["ar:userPrefix"]) {
				$config["ar:userPrefix"] = "yahoo_";
			}
			if (!$config["ar:userDir"]) {
				$config["ar:userDir"]	= "/system/users/";
			}
			if (!$config["ar:userProfile"]) {
				$config["ar:userProfile"]	= null;
			}
			if (!$config["devId"]) {
				$this->error			= "rcas_yahoo: No 'devId' found in rcas_yahoo configuration.";
			}
			$this->config = $config;
		}

		function login() {
			$user = $this->getUser($this->config["ar:userPrefix"]);
			if (!$user) {
				if ($_GET["token_a"]) {
					$userInfo	= $this->getUserInfo($_GET["token_a"]);
					$login		= $this->config["ar:userPrefix"].$userInfo["userData_loginId"];
					$user		= $this->setUser($login, $userInfo);
				} else if ($_GET["statusCode"]) {
					$this->error = $_GET["statusText"];
				} else if (headers_sent()) {
					$this->error	= "rcas_yahoo: could not authenticate user since headers are already sent.";
				} else {
					$url = $this->getAuthTokenURL();
					header("Location: $url");
					return;
				}
			}
			return $user;
		}

		function logout($token) {
			$url	= $this->config["url:logout"];
			$url	.= "?devId=".urlencode($this->config["devId"]);
			$url	.= "&f=qs";
			$url	.= "&a=".urlencode($token);
			$url	.= "&referer=".urlencode($this->config["redirect"]);

			$getInfoRequest = curl_init();
			curl_setopt( $getInfoRequest, CURLOPT_URL, $url );
			curl_setopt( $getInfoRequest, CURLOPT_RETURNTRANSFER, true );
			$response = curl_exec($getInfoRequest);
			if (curl_errno($getInfoRequest)) {
				$this->error = curl_error($getInfoRequest);
			} else {
				curl_close($getInfoRequest);
				parse_str($response, $result);
			}
			return $result;
		}

		function getAuthTokenURL() {
			$url	= $this->config["url:login"];
			$url	.= "?devId=".urlencode($this->config["devId"]);
			$url	.= "&f=qs";
			$url	.= "&succUrl=".urlencode($this->config["redirect"]);
			return	$url;
		}

		function getUserInfo($token) {
			$url	= $this->config["url:info"];
			$url	.= "?devId=".urlencode($this->config["devId"]);
			$url	.= "&f=qs";
			$url	.= "&a=".urlencode($token);
			$url	.= "&referer=".urlencode($this->config["redirect"]);

			$getInfoRequest = curl_init();
			curl_setopt( $getInfoRequest, CURLOPT_URL, $url );
			curl_setopt( $getInfoRequest, CURLOPT_RETURNTRANSFER, true );
			$response = curl_exec($getInfoRequest);
			if (curl_errno($getInfoRequest)) {
				$this->error = curl_error($getInfoRequest);
			} else {
				curl_close($getInfoRequest);
				parse_str($response, $result);
			}
			return $result;

		}

	}
