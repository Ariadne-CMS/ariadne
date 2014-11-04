<?php
	require_once($this->store->get_config('code').'modules/mod_recaptcha/recaptchalib.php');

	class recaptcha {

		function __construct($publicKey, $privateKey, $useSSL = false) {
			$this->publicKey  = $publicKey;
			$this->privateKey = $privateKey;
			$this->useSSL     = $useSSL;
		}


		function check() {
			$result = false;
			$response  = $_POST["recaptcha_response_field"];
			$challenge = $_POST["recaptcha_challenge_field"];
			$address   = $_SERVER["REMOTE_ADDR"];

			$check = recaptcha_check_answer($this->privateKey, $address, $challenge, $response);
			if ($check->is_valid) {
				$result = true;
			} else {
				$result = ar('error')->raiseError($check->error, 301);
			}
			return $result;
		}

		function get($error = null) {
			if ($error && ar('error')->isError($error)) {
				$errorMsg = $error->getMessage();
			} else {
				$errorMsg = $error;
			}
			return recaptcha_get_html($this->publicKey, $errorMsg, $this->useSSL);
		}

		function show($error = null) {
			echo $this->get($error);
		}

	}

	class pinp_recaptcha extends recaptcha {

		function _init($config) {
			if (!$config['publicKey']) {
				return ar('error')->raiseError('mod_recaptcha: no publicKey given', 404);
			}
			if (!$config['privateKey']) {
				return ar('error')->raiseError('mod_recaptcha: no privateKey given', 404);
			}
			return new pinp_recaptcha($config['publicKey'], $config['privateKey'], $config['useSSL']);
		}

		function _check() {
			return $this->check();
		}

		function _get($error = null) {
			return $this->get($error);
		}

		function _show($error = null) {
			return $this->show($error);
		}

	}
?>