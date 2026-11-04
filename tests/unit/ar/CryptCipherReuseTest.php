<?php
class CryptCipherReuseTest extends AriadneBaseTest {
	private function growthWithoutCollection($callback) {
		$callback(); // create the cipher before measuring
		gc_collect_cycles();
		$collecting = gc_enabled();
		gc_disable();
		$before = memory_get_usage();
		for ($i = 0; $i < 200; $i++) {
			$callback();
		}
		$growth = memory_get_usage() - $before;
		if ($collecting) {
			gc_enable();
		}
		gc_collect_cycles();
		return $growth;
	}

	public function testDecryptDoesNotAllocateACipherPerCall() {
		foreach ([0, 1] as $api) {
			$crypt = new ar_crypt(str_repeat('k', 32), MCRYPT_RIJNDAEL_256, $api);
			$encrypted = $crypt->crypt('testme');
			$growth = $this->growthWithoutCollection(function() use ($crypt, $encrypted) {
				$crypt->decrypt($encrypted);
			});
			$this->assertLessThan(1048576, $growth, "api $api");
		}
	}

	public function testNewInstancesDoNotAllocateACipherPerCall() {
		$key = str_repeat('n', 32);
		$encrypted = (new ar_crypt($key, MCRYPT_RIJNDAEL_256, 1))->crypt('testme');
		$growth = $this->growthWithoutCollection(function() use ($key, $encrypted) {
			(new ar_crypt($key, MCRYPT_RIJNDAEL_256, 1))->decrypt($encrypted);
		});
		$this->assertLessThan(1048576, $growth);
	}

	public function testCiphertextDoesNotDependOnEarlierCalls() {
		$crypt = new ar_crypt('frop');
		$encoded = '92ElFVZHAX4kxk5EDgKqcofeg6IS49E1JGQnR4PEWtY=';
		$this->assertEquals('testme', $crypt->decrypt($encoded));
		$crypt->crypt('something else');
		$this->assertEquals('testme', $crypt->decrypt($encoded));
		$this->assertEquals($encoded, $crypt->crypt('testme'));
	}

	public function testInstancesWithTheSameKeyDoNotShareState() {
		$key = str_repeat('s', 32);
		$first = new ar_crypt($key, MCRYPT_RIJNDAEL_256, 1);
		$second = new ar_crypt($key, MCRYPT_RIJNDAEL_256, 1);
		$encrypted = $first->crypt('first value');
		$other = $second->crypt('second value');
		$this->assertEquals('first value', $second->decrypt($encrypted));
		$this->assertEquals('second value', $first->decrypt($other));
		$this->assertEquals('first value', $first->decrypt($encrypted));
	}

	public function testSetKeyUsesTheNewKey() {
		$first = str_repeat('a', 32);
		$second = str_repeat('b', 32);
		$crypt = new ar_crypt($first, MCRYPT_RIJNDAEL_256, 1);
		$encrypted = $crypt->crypt('testme');
		$crypt->setKey($second);
		$this->assertNotEquals('testme', $crypt->decrypt($encrypted));
		$crypt->setKey($first);
		$this->assertEquals('testme', $crypt->decrypt($encrypted));
	}

	public function testManyKeysStayUsableAndBounded() {
		$first = str_repeat('0', 32);
		$encrypted = (new ar_crypt($first, MCRYPT_RIJNDAEL_256, 1))->crypt('testme');
		gc_collect_cycles();
		$before = memory_get_usage();
		for ($i = 0; $i < 100; $i++) {
			$key = str_pad((string) $i, 32, 'x');
			$crypt = new ar_crypt($key, MCRYPT_RIJNDAEL_256, 1);
			$this->assertEquals("value $i", $crypt->decrypt($crypt->crypt("value $i")));
		}
		unset($crypt);
		gc_collect_cycles();
		$this->assertLessThan(4194304, memory_get_usage() - $before);
		$this->assertEquals('testme', (new ar_crypt($first, MCRYPT_RIJNDAEL_256, 1))->decrypt($encrypted));
	}
}
