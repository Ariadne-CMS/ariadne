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

	public function testReadsRecryptAesFixture() {
		// recrypt.php: zero-pad the legacy key, take its first 32 bytes, AES-256-CBC.
		$encoded = '(2.0,AAECAwQFBgcICQoLDA0ODw==)D/mhKOfuX3fDOMQ1rnJx9Q==';
		$this->assertSame('testme', (new ar_crypt('frop'))->decrypt($encoded));
	}

	public function testNewWritesUseAesForBothLegacyApis() {
		foreach ([0, 1] as $api) {
			$crypt = new ar_crypt(str_repeat('k', 32), MCRYPT_RIJNDAEL_256, $api);
			foreach (['', 'testme', "\0binary\0", str_repeat('x', 1000), 'Gebruiker ë'] as $text) {
				$encoded = $crypt->crypt($text);
				$this->assertStringStartsWith('(2.0,', $encoded);
				$this->assertSame($text, $crypt->decrypt($encoded));
				// Independently read the output using recrypt.php's wire format.
				preg_match('/^\(2\.0,([^)]+)\)(.+)$/', $encoded, $parts);
				$this->assertSame($text, openssl_decrypt($parts[2], 'aes-256-cbc', str_repeat('k', 32), 0, base64_decode($parts[1])));
			}
			$this->assertNotSame($crypt->crypt('same'), $crypt->crypt('same'));
		}
	}

	public function testDefaultSaltMatchesMigrationKeyHandling() {
		global $AR;
		$salt = $AR->sgSalt;
		try {
			foreach ([1, 16, 17, 24, 25, 32, 40] as $length) {
				$AR->sgSalt = str_repeat('s', $length);
				$crypt = new ar_crypt();
				$iv = str_repeat('i', 16);
				$key = str_pad(substr($AR->sgSalt, 0, 32), 32, "\0");
				$encoded = '(2.0,' . base64_encode($iv) . ')' . openssl_encrypt('migrated', 'aes-256-cbc', $key, 0, $iv);
				$this->assertSame('migrated', $crypt->decrypt($encoded));
				$this->assertSame('fresh', $crypt->decrypt($crypt->crypt('fresh')));
			}
		} finally {
			$AR->sgSalt = $salt;
		}
	}

	public function testSetKeyMatchesConstructorAtLegacyKeyBoundaries() {
		foreach ([16, 24, 32, 40] as $length) {
			$key = str_repeat('k', $length);
			$writer = new ar_crypt($key);
			$reader = new ar_crypt('other');
			$reader->setKey($key);
			$this->assertSame('testme', $reader->decrypt($writer->crypt('testme')));
		}
	}

	public function testRejectsMalformedOrUnsupportedVersionedValues() {
		$crypt = new ar_crypt('frop');
		foreach ([
			'(3.0,AAECAwQFBgcICQoLDA0ODw==)D/mhKOfuX3fDOMQ1rnJx9Q==',
			'(2.0)broken', '(2.0,!!!!)broken',
			'(2.0,YQ==)D/mhKOfuX3fDOMQ1rnJx9Q==',
			'(2.0,AAECAwQFBgcICQoLDA0ODw==)',
			'(2.0,AAECAwQFBgcICQoLDA0ODw==)!!!!',
			'(2.0,AAECAwQFBgcICQoLDA0ODw==)YQ==',
		] as $encoded) {
			$this->assertFalse($crypt->decrypt($encoded), $encoded);
		}
	}
}
