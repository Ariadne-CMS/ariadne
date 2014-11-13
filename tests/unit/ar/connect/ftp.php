<?php
class ar_connect_ftpTest extends AriadneBaseTest {
	public function testFTPGet() {
		$content = ar('connect/ftp')->get('ftp://ftp.snt.utwente.nl/pub/test/1M');
		$this->assertEquals(1000000, strlen($content));
	}

	public function testFTPGetNonExisting() {
		$content = ar('connect/ftp')->get('ftp://ftp.snt.utwente.nl/pub/test/DOESNTEXIST');
		$this->assertInstanceOf('ar_error', $content);
	}
}
