<?php

class psiteUrlSaveTtest extends AriadneBaseTest
{

	static protected $testPath;

	public function setUp()
	{
		$this->initAriadne();
	}

	public static function setUpBeforeClass()
	{
		self::initAriadne();
		parent::setUpBeforeClass();
		$args = array (
			'arNewType' => 'psite',
			'arNewFilename' => '{5:id}',
			'nl' => array (
				'name' => 'testsite nl',
			),
			'en' => array (
				'name' => 'testsite en',
			),
			'de' => array (
				'name' => 'testsite de',
			)
		);

		self::$testPath = current(ar::get(TESTBASE)->call('system.new.phtml' , $args));
	}

	public function testWithoutURL(){
		$testobj = current(ar::get(self::$testPath)->call('system.get.phtml'));

		$testobj->call('system.save.data.phtml',array());
		$this->assertFalse((bool)$testobj->error);

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));

		$this->assertEquals($testobj->data->url, $refobj->data->url);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->en->urlList, $refobj->data->en->urlList);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertEmpty($refobj->data->config->url_list['host']);

		$this->assertArrayHasKey('nls', $refobj->data->config->url_list);

	}

	public function testNoNlsUpgrade(){
		$testobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$testobj->data->url = 'http://test.url/';

		// unset structure
		unset($testobj->data->urlList);
		unset($testobj->data->en->url);
		unset($testobj->data->de->url);
		unset($testobj->data->nl->url);
		unset($testobj->data->en->urlList);
		unset($testobj->data->de->urlList);
		unset($testobj->data->nl->urlList);
		$testobj->save();

		// renew testobject
		$testobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$testobj->call('system.save.data.phtml',array());
		$this->assertFalse((bool)$testobj->error);

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$this->assertEquals($testobj->data->url, 'http://test.url/');
		$this->assertEquals($testobj->data->url, $refobj->data->url);

		$this->assertContains('http://test.url/', $refobj->data->en->urlList);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->en->urlList, $refobj->data->en->urlList);
		$this->assertEmpty($testobj->data->nl->urlList);
		$this->assertEmpty($testobj->data->de->urlList);
		$this->assertEquals('', $testobj->data->nl->url);
		$this->assertEquals('', $testobj->data->de->url);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertArrayHasKey('test.url', $refobj->data->config->url_list['host']);
	}

	public function testMultiNlsUpgrade(){
		$testobj = current(ar::get(self::$testPath)->call('system.get.phtml'));

		// test data
		$testobj->data->en->url = 'http://test.url.en';
		$testobj->data->de->url = 'http://test.url.de';
		$testobj->data->nl->url = 'http://test.url.nl';

		// unset structure
		unset($testobj->data->url);
		unset($testobj->data->en->urlList);
		unset($testobj->data->de->urlList);
		unset($testobj->data->nl->urlList);
		$testobj->save();

		// renew testobject
		$testobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$testobj->call('system.save.data.phtml',array());
		$this->assertFalse((bool)$testobj->error);

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$this->assertEquals($testobj->data->url, 'http://test.url.en');
		$this->assertEquals($testobj->data->en->url, 'http://test.url.en');
		$this->assertEquals($testobj->data->nl->url, 'http://test.url.nl');
		$this->assertEquals($testobj->data->de->url, 'http://test.url.de');
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->nl->url, $refobj->data->nl->url);
		$this->assertEquals($testobj->data->de->url, $refobj->data->de->url);

		$this->assertContains('http://test.url.en', $refobj->data->en->urlList);
		$this->assertContains('http://test.url.nl', $refobj->data->nl->urlList);
		$this->assertContains('http://test.url.de', $refobj->data->de->urlList);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->nl->url, $refobj->data->nl->url);
		$this->assertEquals($testobj->data->de->url, $refobj->data->de->url);
		$this->assertEquals($testobj->data->en->urlList, $refobj->data->en->urlList);
		$this->assertEquals($testobj->data->nl->urlList, $refobj->data->nl->urlList);
		$this->assertEquals($testobj->data->de->urlList, $refobj->data->de->urlList);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertArrayHasKey('test.url.en', $refobj->data->config->url_list['host']);
		$this->assertArrayHasKey('test.url.nl', $refobj->data->config->url_list['host']);
		$this->assertArrayHasKey('test.url.de', $refobj->data->config->url_list['host']);
		$this->assertEquals('en', $refobj->data->config->url_list['host']['test.url.en']);
		$this->assertEquals('nl', $refobj->data->config->url_list['host']['test.url.nl']);
		$this->assertEquals('de', $refobj->data->config->url_list['host']['test.url.de']);
	}

	public function testApiNoNLS(){
		$testobj = current(ar::get(self::$testPath)->call('system.get.phtml'));

		// unset structure
		unset($testobj->data->url);
		unset($testobj->data->urlList);
		unset($testobj->data->en->url);
		unset($testobj->data->de->url);
		unset($testobj->data->nl->url);
		unset($testobj->data->en->urlList);
		unset($testobj->data->de->urlList);
		unset($testobj->data->nl->urlList);
		$testobj->save();

		$testobj->call('system.save.data.phtml',array(
			'url' => 'http://test.url'
		));
		$this->assertFalse((bool)$testobj->error);

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$this->assertEquals($testobj->data->url, 'http://test.url');
		$this->assertEquals($testobj->data->url, $refobj->data->url);

		$this->assertContains('http://test.url', $refobj->data->en->urlList);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->en->urlList, $refobj->data->en->urlList);
		$this->assertEmpty($testobj->data->nl->urlList);
		$this->assertEmpty($testobj->data->de->urlList);
		$this->assertEquals('', $testobj->data->nl->url);
		$this->assertEquals('', $testobj->data->de->url);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertArrayHasKey('test.url', $refobj->data->config->url_list['host']);

		$testobj->call('system.save.data.phtml',array(
			'url' => ''
		));
		$this->assertFalse((bool)$testobj->error);

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$this->assertEquals($testobj->data->url, '');
		$this->assertEquals($testobj->data->url, $refobj->data->url);

		$this->assertEmpty($refobj->data->en->urlList);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->en->urlList, $refobj->data->en->urlList);
		$this->assertEmpty($testobj->data->nl->urlList);
		$this->assertEmpty($testobj->data->de->urlList);
		$this->assertEquals('', $testobj->data->nl->url);
		$this->assertEquals('', $testobj->data->de->url);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertArrayNotHasKey('test.url', $refobj->data->config->url_list['host']);

	}

	public function testApiNLS(){
		$testobj = current(ar::get(self::$testPath)->call('system.get.phtml'));

		// unset structure
		unset($testobj->data->url);
		unset($testobj->data->urlList);
		unset($testobj->data->en->url);
		unset($testobj->data->de->url);
		unset($testobj->data->nl->url);
		unset($testobj->data->en->urlList);
		unset($testobj->data->de->urlList);
		unset($testobj->data->nl->urlList);
		$testobj->save();

		$testobj->call('system.save.data.phtml',array(
			'en' => array (
				'url' => 'http://test.url.en'
			),
			'nl' => array (
				'url' => 'http://test.url.nl'
			)
		));
		$this->assertFalse((bool)$testobj->error);

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$this->assertEquals($testobj->data->url, 'http://test.url.en');
		$this->assertEquals($testobj->data->en->url, 'http://test.url.en');
		$this->assertEquals($testobj->data->nl->url, 'http://test.url.nl');
		$this->assertEquals($testobj->data->url, $refobj->data->url);

		$this->assertContains('http://test.url.en', $refobj->data->en->urlList);
		$this->assertContains('http://test.url.nl', $refobj->data->nl->urlList);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->en->urlList, $refobj->data->en->urlList);
		$this->assertEmpty($testobj->data->de->urlList);
		$this->assertEquals('', $testobj->data->de->url);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertArrayHasKey('test.url.nl', $refobj->data->config->url_list['host']);

		$testobj->call('system.save.data.phtml',array(
			'nl' => array (
				'url' => 'http://test2.url.nl'
			)
		));
		$this->assertFalse((bool)$testobj->error);

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$this->assertEquals($testobj->data->url, 'http://test.url.en');
		$this->assertEquals($testobj->data->en->url, 'http://test.url.en');
		$this->assertEquals($testobj->data->nl->url, 'http://test2.url.nl');
		$this->assertEquals($testobj->data->url, $refobj->data->url);
		$this->assertEquals($testobj->data->nl->url, $refobj->data->nl->url);

		$this->assertContains('http://test.url.en', $refobj->data->en->urlList);
		$this->assertContains('http://test.url.nl', $refobj->data->nl->urlList);
		$this->assertContains('http://test2.url.nl', $refobj->data->nl->urlList);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->en->urlList, $refobj->data->en->urlList);
		$this->assertEmpty($testobj->data->de->urlList);
		$this->assertEquals('', $testobj->data->de->url);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertArrayHasKey('test2.url.nl', $refobj->data->config->url_list['host']);
		$this->assertArrayHasKey('test.url.nl', $refobj->data->config->url_list['host']);
		$this->assertArrayHasKey('test.url.en', $refobj->data->config->url_list['host']);

		$testobj->call('system.save.data.phtml',array(
			'nl' => array (
				'url' => 'http://test.url.nl'
			)
		));
		$this->assertFalse((bool)$testobj->error);

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$this->assertEquals($testobj->data->en->url, 'http://test.url.en');
		$this->assertEquals($testobj->data->nl->url, 'http://test.url.nl');
		$this->assertEquals($testobj->data->nl->url, $refobj->data->nl->url);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);

		$this->assertContains('http://test.url.nl', $refobj->data->nl->urlList);
		$this->assertContains('http://test2.url.nl', $refobj->data->nl->urlList);
		$this->assertEquals($testobj->data->nl->urlList, $refobj->data->nl->urlList);
		$this->assertEmpty($testobj->data->de->urlList);
		$this->assertEquals('', $testobj->data->de->url);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertArrayHasKey('test2.url.nl', $refobj->data->config->url_list['host']);
		$this->assertArrayHasKey('test.url.nl', $refobj->data->config->url_list['host']);
		$this->assertArrayHasKey('test.url.en', $refobj->data->config->url_list['host']);

		$testobj->call('system.save.data.phtml',array(
			'en' => array (
				'url' => ''
			),
			'nl' => array (
				'url' => ''
			)
		));

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$this->assertEquals($testobj->data->url, '');
		$this->assertEquals($testobj->data->nl->url, '');
		$this->assertEquals($testobj->data->en->url, '');
		$this->assertEquals($testobj->data->url, $refobj->data->url);

		$this->assertEmpty($refobj->data->en->urlList);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->en->urlList, $refobj->data->en->urlList);
		$this->assertEmpty($testobj->data->nl->urlList);
		$this->assertEmpty($testobj->data->de->urlList);
		$this->assertEquals('', $testobj->data->nl->url);
		$this->assertEquals('', $testobj->data->de->url);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertArrayNotHasKey('test.url.nl', $refobj->data->config->url_list['host']);
		$this->assertArrayNotHasKey('test2.url.nl', $refobj->data->config->url_list['host']);
		$this->assertArrayNotHasKey('test.url.en', $refobj->data->config->url_list['host']);

	}

	public function testApiMultiUrlList(){
		$testobj = current(ar::get(self::$testPath)->call('system.get.phtml'));

		// unset structure
		unset($testobj->data->url);
		unset($testobj->data->urlList);
		unset($testobj->data->en->url);
		unset($testobj->data->de->url);
		unset($testobj->data->nl->url);
		unset($testobj->data->en->urlList);
		unset($testobj->data->de->urlList);
		unset($testobj->data->nl->urlList);
		$testobj->save();

		$testobj->call('system.save.data.phtml',array(
			'en' => array (
				'urlList' => array (
					'http://test.url.en',
					)
			),
			'nl' => array (
				'urlList' => array (
					'http://test.url.nl',
					'http://test2.url.nl'
				)
			)
		));
		$this->assertFalse((bool)$testobj->error);

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$this->assertEquals($testobj->data->url, 'http://test.url.en');
		$this->assertEquals($testobj->data->en->url, 'http://test.url.en');
		$this->assertEquals($testobj->data->nl->url, 'http://test.url.nl');
		$this->assertEquals($testobj->data->url, $refobj->data->url);

		$this->assertContains('http://test.url.en', $refobj->data->en->urlList);
		$this->assertContains('http://test.url.nl', $refobj->data->nl->urlList);
		$this->assertContains('http://test2.url.nl', $refobj->data->nl->urlList);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->en->urlList, $refobj->data->en->urlList);
		$this->assertEquals($testobj->data->nl->url, $refobj->data->nl->url);
		$this->assertEquals($testobj->data->nl->urlList, $refobj->data->nl->urlList);
		$this->assertEmpty($testobj->data->de->urlList);
		$this->assertEquals('', $testobj->data->de->url);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertArrayHasKey('test.url.en', $refobj->data->config->url_list['host']);
		$this->assertArrayHasKey('test.url.nl', $refobj->data->config->url_list['host']);
		$this->assertArrayHasKey('test2.url.nl', $refobj->data->config->url_list['host']);

		$testobj->call('system.save.data.phtml',array(
			'en' => array (
				'urlList' => ''
			)
		));

		$refobj = current(ar::get(self::$testPath)->call('system.get.phtml'));
		$this->assertEquals($testobj->data->url, '');
		$this->assertEquals($testobj->data->nl->url, 'http://test.url.nl');
		$this->assertEquals($testobj->data->en->url, '');
		$this->assertEquals($testobj->data->url, $refobj->data->url);

		$this->assertEmpty($refobj->data->en->urlList);
		$this->assertEquals($testobj->data->en->url, $refobj->data->en->url);
		$this->assertEquals($testobj->data->en->urlList, $refobj->data->en->urlList);
		$this->assertEmpty($testobj->data->de->urlList);
		$this->assertEquals('', $testobj->data->de->url);

		$this->assertArrayHasKey('host', $refobj->data->config->url_list);
		$this->assertArrayHasKey('test.url.nl', $refobj->data->config->url_list['host']);
		$this->assertArrayHasKey('test2.url.nl', $refobj->data->config->url_list['host']);
		$this->assertArrayNotHasKey('test.url.en', $refobj->data->config->url_list['host']);
	}
}
?>
