<?php
	ar_pinp::allow( 'ar_crypt' );

	class ar_crypt extends arBase {
		private $key;

		public function __construct( $key = '', $encoding = MCRYPT_RIJNDAEL_256 ) {
			global $AR;
			if ($key == '') {
				$key = $AR->sgSalt;
			}
			$this->key = $key;
			$this->encoding = $encoding;
		}

		public function setKey( $key ) {
			$this->key = $key;
		}

		public function crypt( $value ) {
			return base64_encode(@mcrypt_encrypt($this->encoding, $this->key, $value, MCRYPT_MODE_CBC));
		}

		public function decrypt( $value ) {
			return trim(@mcrypt_decrypt($this->encoding, $this->key, base64_decode($value), MCRYPT_MODE_CBC), "\0");
		}
	}
