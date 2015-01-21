<?php
class ar_cryptTest extends AriadneBaseTest {
	public function testOldApi() {

		$text = "testme";
		$key  = "frop";

		$crypt = new ar_crypt($key);
		$encoded = $crypt->crypt($text);
		$decoded = $crypt->decrypt($encoded);
		$this->assertEquals($text, $decoded);
	}

	public function testOldApiphp56Compat() {
		$text = "testme";
		$key  = "frop";
		$encoded = "92ElFVZHAX4kxk5EDgKqcofeg6IS49E1JGQnR4PEWtY=";

		$crypt = new ar_crypt($key);
		$decoded = $crypt->decrypt($encoded);
		$this->assertEquals($text, $decoded);
	}

	public function testNewApiSetKey() {
		$text = "testme";
		$key  = "frop";
		$salt = sha1("saltgeneration");

		$crypt = new ar_crypt($key,MCRYPT_RIJNDAEL_256,1);

		$generatedkey = $crypt->pbkdf2($key,$salt);
		$crypt->setKey($generatedkey);

		$encoded = $crypt->crypt($text);
		$decoded = $crypt->decrypt($encoded);

		$this->assertEquals($text, $decoded);
	}

	public function testNewApi() {
		$text = "testme";
		$key  = "frop";
		$salt = sha1("saltgeneration");

		$crypt = new ar_crypt($key,MCRYPT_RIJNDAEL_256,1);

		$key = $crypt->pbkdf2($key,$salt);

		$crypt = new ar_crypt($key,MCRYPT_RIJNDAEL_256,1);
		$encoded = $crypt->crypt($text);
		$decoded = $crypt->decrypt($encoded);

		$this->assertEquals($text, $decoded);
	}
}
