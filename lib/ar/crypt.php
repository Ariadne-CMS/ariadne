<?php
	ar_pinp::allow( 'ar_crypt' );

	class ar_crypt extends arBase {
		private $key;
		private $encoding;
		private $iv;
		private $api;
		private $mode = MCRYPT_MODE_CBC;

		public function __construct( $key = null, $encoding = MCRYPT_RIJNDAEL_256, $api=0) {
			global $AR;
			$this->encoding = $encoding;
			$this->api      = $api;
			$this->key      = $this->key;

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

		public function crypt( $value ) {
			if ($this->api === 0 ) {
				$iv = str_pad('',mcrypt_get_iv_size ( $this->encoding, $this->mode),"\0");
				return base64_encode(mcrypt_encrypt($this->encoding, $this->key, $value, $this->mode,$iv));

			} else {
				$ivsize = mcrypt_get_iv_size ( $this->encoding, $this->mode);
				$iv = mcrypt_create_iv ( $ivsize , MCRYPT_DEV_URANDOM);

				$encrypted = mcrypt_encrypt($this->encoding, $this->key, $value, $this->mode,$iv);
				return base64_encode($iv . $encrypted);
			}
		}

		public function decrypt( $value ) {
			if ($this->api === 0 ) {
				$iv = str_pad('',mcrypt_get_iv_size ( $this->encoding, $this->mode),"\0");
				return trim(mcrypt_decrypt($this->encoding, $this->key, base64_decode($value), $this->mode,$iv), "\0");

			} else {
				$decoded = base64_decode($value);
				$ivsize = mcrypt_get_iv_size ( $this->encoding, $this->mode);
				$iv = substr($decoded, 0, $ivsize);

				$encrypted = substr($decoded, $ivsize);
				return trim(mcrypt_decrypt($this->encoding, $this->key, $encrypted, $this->mode,$iv), "\0");
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
