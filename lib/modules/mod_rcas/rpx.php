<?php
	class rcas_rpx extends rcas {
		function get_result_string($result_code) {
			$result_codes = array();

			if ($result_codes[$result_code]) {
				return $result_codes[$result_code];
			}
		}

		function _login() {
			return $this->login();
		}

		function _logout() {
			return $this->logout();
		}

		function __construct($config) {
			if (!$config["app_id"]) {
				$this->error			= "rcas_rpx: No 'app_id' found in rcas_rpx configuration.";
			}
			if (!$config["api_key"]) {
				$this->error			= "rcas_rpx: No 'api_key' found in rcas_rpx configuration.";
			}
			if (!$config["redirect"]) {
				$config["redirect"] = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
			}
			if (!$config["url:login"]) {
				$config["url:login"]	= "https://" . $config['app_id'] . ".rpxnow.com/openid/embed?token_url=" . urlencode($config['redirect']);
			}
 			if (!$config["url:logout"]) {
				$config["url:logout"]	= $config['redirect'];
			}
			if (!$config["url:info"]) {
				$config["url:info"]		= 'https://rpxnow.com/api/v2/auth_info';
			}
			if (!$config["url:map"]) {
				$config["url:map"]		= 'https://rpxnow.com/api/v2/map';
			}
			if (!$config["ar:userPrefix"]) {
				$config["ar:userPrefix"] = "rpx_";
			}
			if (!$config["ar:userDir"]) {
				$config["ar:userDir"]   = "/system/users/";
			}
			if (!$config["ar:userProfile"]) {
				$config["ar:userProfile"]   = null;
			}
			$this->config = $config;
		}

		function login() {
			$user = $this->getUser($this->config["ar:userPrefix"]);
			if (!$user) {
				if ($_POST["token"]) {
					$userInfo	= $this->getUserInfo($_POST["token"]);
					if ($userInfo->stat == 'ok') {

						// FIXME: The 'basic' API does not provide the
						// primary key, we need to make a mapping with the
						// profile identifier. Problem: the ID used by
						// Google is longer than 32 characters which is not
						// allowed for usernames in Ariadne.


/*						if ($userInfo->profile->primaryKey) {
							$login = $userInfo->profile->primaryKey;
						} else {
							$login		= $this->config["ar:userPrefix"]."{5:id}";
						}
*/

						$login		 = $this->config["ar:userPrefix"] . substr($userInfo->profile->identifier, -24); // FIXME: this is a hack!
						$user		= $this->setUser($login, $userInfo);
//						if (!$userInfo->profile->primaryKey) {
//							$mapping = $this->mapUser(basename($user->path), $userInfo->profile->identifier);
//						}
						return $user;
					} else {
						$this->error = $userInfo->stat;
					}
				} else {
					$url = $this->getAuthTokenURL();
					echo '<iframe src="' . $url . '" scrolling="no" frameBorder="no" class="rpx_login" style="width:400px;height:240px;"></iframe>';
					return false;
				}
			}
			return $user;
		}

		function logout($token) {
			// FIXME: implement logout
			return false;
		}

		function getAuthTokenURL() {
			$url	= $this->config["url:login"];
			$url 	.= "&token_url=" . urlencode($this->config["redirect"]);

			return $url;
		}

		function getUserInfo($token) {
			$url	= $this->config["url:info"];
			$post_data = array(
				'token' => $token,
				'apiKey' => $this->config['api_key'],
				'format' => 'json'
			);

			$getInfoRequest = curl_init();
			curl_setopt( $getInfoRequest, CURLOPT_URL, $url );
			curl_setopt( $getInfoRequest, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $getInfoRequest, CURLOPT_POST, true);
			curl_setopt( $getInfoRequest, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt( $getInfoRequest, CURLOPT_HEADER, false);
			curl_setopt( $getInfoRequest, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($getInfoRequest);
			if (curl_errno($getInfoRequest)) {
				$this->error = curl_error($getInfoRequest);
			} else {
				curl_close($getInfoRequest);
				$result = json_decode($response);
			}
			return $result;
		}

		function mapUser($username, $identifier) {
			$url = $this->config['url:map'];
			$url .= "?apiKey=" . $this->config['api_key'];

			$post_data = array(
				"apiKey" => $this->config['api_key'],
				"format" => "json",
				"identifier" => $identifier,
				"primaryKey" => $username
			);

			print_r($post_data);
			$mapUserRequest = curl_init();
			curl_setopt( $mapUserRequest, CURLOPT_URL, $url );
			curl_setopt( $mapUserRequest, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $mapUserRequest, CURLOPT_POST, true);
			curl_setopt( $mapUserRequest, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt( $mapUserRequest, CURLOPT_HEADER, false);
			curl_setopt( $mapUserRequest, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($mapUserRequest);
			if (curl_errno($mapUserRequest)) {
				$this->error = curl_error($mapUserRequest);
			} else {
				curl_close($mapUserRequest);
				$result = json_decode($response);
			}
			print_r($result);
			return $result;
		}
	}
