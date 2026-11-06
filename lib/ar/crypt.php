<?php
	ar_pinp::allow( 'ar_crypt' );

	class ar_crypt extends arBase {
		private $key;
		private $encoding;
		private $iv;
		private $api;
		private $mode = MCRYPT_MODE_CBC;
		private const AES_ENCODING = 'aes-256-cbc';
		private const MAX_CIPHERS = 8;
		private static $ciphers = [];

		public function __construct( $key = null, $encoding = MCRYPT_RIJNDAEL_256, $api=0) {
			global $AR;
			$this->encoding = $encoding;
			$this->api      = $api;
			$this->key      = $key;

			if ($this->api === 0 ) {
				// this api will be deprecated in the future
				// we also try to be backwards compatible with pre php 5.6

				if ($key == null) {
					$key = $AR->sgSalt;
				}

				$keysizes = mcrypt_module_get_supported_key_sizes($this->encoding, $this->mode);
				sort($keysizes);

				$keysize = end ($keysizes);
				foreach($keysizes as $keysize){
					if($keysize >= strlen($key)) {
						break;
					}
				}

				$this->key = str_pad(substr($key,0,$keysize), $keysize, "\0");
			}
		}

		public function setApi($api) {
			$this->api = $api;
		}

		public function setKey( $key ) {
			if ($this->api === 0 ) {
				$keysizes = mcrypt_module_get_supported_key_sizes($this->encoding, $this->mode);
				sort($keysizes);

				$keysize = end ($keysizes);
				foreach($keysizes as $keysize){
					if($keysize > strlen($key)) {
						break;
					}
				}
				$this->key = str_pad(substr($key,0,$keysize), $keysize, "\0");

			} else {
				$this->key = $key;
			}
		}

		/*
			mcrypt_encrypt() and mcrypt_decrypt() create a new cipher on every call. With mcrypt_compat
			each cipher compiles its own code, and only the cycle collector can free that memory.
			So keep one cipher per key, shared by all instances, and only change its iv. The iv is set
			on every call, so nothing carries over between calls. The cache is emptied when it is full,
			so code that uses many keys can't grow it without limit.
			Keys mcrypt_encrypt() would refuse get no cipher, the callers then use the original
			functions, which report the error.
		*/
		private function getCipher($iv) {
			$id = $this->encoding . ':' . $this->mode . ':' . $this->key;
			if (isset(self::$ciphers[$id])) {
				self::$ciphers[$id]->setIV($iv);
				return self::$ciphers[$id];
			}
			$keysizes = mcrypt_module_get_supported_key_sizes($this->encoding);
			if (!in_array(strlen($this->key ?? ''), $keysizes)) {
				return false;
			}
			$cipher = mcrypt_module_open($this->encoding, '', $this->mode, '');
			if ($cipher) {
				mcrypt_generic_init($cipher, $this->key, $iv);
				if (is_object($cipher)) {
					if (count(self::$ciphers) >= self::MAX_CIPHERS) {
						self::$ciphers = [];
					}
					self::$ciphers[$id] = $cipher;
				}
			}
			return $cipher;
		}

		private function decryptValue($value, $iv) {
			$cipher = $this->getCipher($iv);
			if ($cipher) {
				return mdecrypt_generic($cipher, $value);
			}
			return mcrypt_decrypt($this->encoding, $this->key, $value, $this->mode, $iv);
		}

		// New values use the same versioned AES format as recrypt.php. The encoding
		// and api constructor arguments select how unversioned legacy values are read.
		public function crypt( $value ) {
			$iv = random_bytes(openssl_cipher_iv_length(self::AES_ENCODING));
			$encrypted = openssl_encrypt($value, self::AES_ENCODING, substr($this->key ?? '', 0, 32), 0, $iv);
			if ($encrypted === false) {
				return false;
			}
			return '(2.0,' . base64_encode($iv) . ')' . $encrypted;
		}

		public function decrypt( $value ) {
			// A versioned value must never fall back to legacy decryption.
			if (str_starts_with($value ?? '', '(')) {
				if (!preg_match('/\A\(2\.0,([A-Za-z0-9+\/=]+)\)([A-Za-z0-9+\/=]+)\z/', $value, $matches)) {
					return false;
				}
				$iv = base64_decode($matches[1], true);
				$encrypted = base64_decode($matches[2], true);
				if ($iv === false || strlen($iv) !== openssl_cipher_iv_length(self::AES_ENCODING)
					|| $encrypted === false || strlen($encrypted) === 0 || strlen($encrypted) % 16 !== 0) {
					return false;
				}
				return openssl_decrypt($encrypted, self::AES_ENCODING, substr($this->key ?? '', 0, 32), OPENSSL_RAW_DATA, $iv);
			}

			if ($this->api === 0 ) {
				$iv = str_pad('',mcrypt_get_iv_size ( $this->encoding, $this->mode),"\0");
				return trim($this->decryptValue(base64_decode($value), $iv), "\0");

			} else {
				$decoded = base64_decode($value??'');
				$ivsize = mcrypt_get_iv_size ( $this->encoding, $this->mode);
				$iv = substr($decoded, 0, $ivsize);
				if($ivsize != strlen($iv)) {
					return false;
				}

				$encrypted = substr($decoded, $ivsize);
				return trim($this->decryptValue($encrypted, $iv), "\0");
			}
		}

		public function pbkdf2($password, $salt, $count=5000, $key_length = null, $raw_output = true) {
			$algorithm = 'sha512';

			if(is_null($key_length)) {
				$key_length = mcrypt_get_key_size ( $this->encoding, $this->mode );
			}

			$algorithm = strtolower($algorithm);
			if(!in_array($algorithm, hash_algos(), true)){
				// fixme, return ar('error');
				trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
			}
			if($count <= 0 || $key_length <= 0) {
				// fixme, return ar('error');
				trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);
			}

			if (function_exists("hash_pbkdf2")) {
				// The output length is in NIBBLES (4-bits) if $raw_output is false!
				if (!$raw_output) {
					$key_length = $key_length * 2;
				}
				return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
			}

			$hash_length = strlen(hash($algorithm, "", true));
			$block_count = ceil($key_length / $hash_length);

			$output = "";
			for($i = 1; $i <= $block_count; $i++) {
				// $i encoded as 4 bytes, big endian.
				$last = $salt . pack("N", $i);
				// first iteration
				$last = $xorsum = hash_hmac($algorithm, $last, $password, true);
				// perform the other $count - 1 iterations
				for ($j = 1; $j < $count; $j++) {
					$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
				}
				$output .= $xorsum;
			}

			if($raw_output){
				return substr($output, 0, $key_length);
			} else {
				return base64_encode(substr($output, 0, $key_length));
			}
		}
	}
