<?php

/*
 * crypto library
 * design choices:
 * - never return the exceptions from the lower levels
 * - keys creation from pre generated keys is done with a closure to inject in into a Key object
 *   and not by using the 'insecure' method in the defuse library
 */

ar_pinp::allow('ar_security_crypt');

use Defuse\Crypto as DC;

class ar_security_crypt extends arBase {
	private $secret;
	private $method;
	const USEKEY  = 1;
	const USEPASS = 2;

	private function keyRawToInternal($rawkey) {
		static $key;
		static $func;
		if (is_null($key)) {
			$key = DC\Key::createNewRandomKey();
			$func = function($rawkey) {
				return new self($rawkey);
			};
			$func = $func->bindto($key,$key);
		}
		return $func($rawkey);
	}

	public function __construct() {
	}

	public function key($key) {
		$key = base64_decode($key,true);
		if ($key === false) {
			return ar('error')->raiseError('Invalid key encoding',1000);
		}

		try {
			$key = $this->keyRawToInternal($key);
		} catch (DC\Exception\EnvironmentIsBrokenException $ex) {
			return ar('error')->raiseError('Invalid key', 1000);
		}
		$ret = new self();
		// hide key from most error related stackdumps
		$ret->secret = function() use ($key) { return $key; };
		$ret->method = self::USEKEY;
		return $ret;
	}

	public function passphrase($pass)
	{
		$ret = new self();
		// hide pass from most error related stackdumps
		$ret->secret = function() use ($pass) { return $pass; };
		$ret->method = self::USEPASS;
		return $ret;
	}

	/*
	 * keybytes are hardcoded to 32 bytes
	 * 3 options:
	 * A) base64 encoded key
	 * B) key in the format for defuse
	 * C) not a key
	 * Only A and C are 'implemented'
	 */

	public function encrypt($data) {
		if (!isset($this->method)) {
			return ar('error')->raiseError('use key or passphrase to init crypt engine',1000);
		}

		if (!is_scalar($data)) {
			return ar('error')->raiseError('Data should be a scalar datatype',1000);
		}

		try {
			$secret = $this->secret;
			if ($this->method === self::USEKEY ) {
				$ciphertext = DC\Crypto::encrypt($data, $secret(), true);
			} else {
				$ciphertext = DC\Crypto::encryptWithPassword($data, $secret(), true);

			}
		} catch (DC\Exception\EnvironmentIsBrokenException $ex) {
			return ar('error')->raiseError('enviroment is broken', 1000);
		}

		return base64_encode($ciphertext);
	}

	public function decrypt($data){
		if (!isset($this->method)) {
			return ar('error')->raiseError('use key or passphrase to init crypt engine',1000);
		}
		$data = base64_decode($data,true);

		if ($data === false ) {
			return ar('error')->raiseError('Invalid data, not properly base64 encoded',1000);
		}

		try {
			$secret = $this->secret;
			if ($this->method === self::USEKEY ) {
				$decrypted = DC\Crypto::decrypt($data, $secret(), true);
			} else {
				$decrypted = DC\Crypto::decryptWithPassword($data, $secret(), true);
			}
		} catch (DC\Exception\EnvironmentIsBrokenException $ex) {
			return ar('error')->raiseError('enviroment is broken', 1000);
		} catch (DC\Exception\WrongKeyOrModifiedCiphertextException $ex) {
			return ar('error')->raiseError('wrong key of data has been tampered with', 1000);
		}
		return $decrypted;
	}

	public function generateKey(){
		$key   = DC\Key::createNewRandomKey();
		$bytes = $key->getRawBytes();
		return base64_encode($bytes);
	}

}
