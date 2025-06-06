<?php

class svnTest extends AriadneBaseTest
{
	private static $repo;
	private static $testpath;

	public function setUp(): void
	{
		if( !getenv('svnrepo') ) {
			$this->marktestskipped(
				'Skipping SVN test, no svn repo configured'
			);
			return;
		}

		$this->initAriadne();
	}

	public static function setUpBeforeClass(): void
	{
		self::$repo = getenv('svnrepo') . '/testlibrary/';
	}


	public function helperCheckout($revision=false) {
		$args = array (
				'repository' => self::$repo,
				'username'   => 'test',
				'password'   => '',
				'checkunder' => false,
				'revision'   => $revision,
		);
		ob_start();
		$res = ar::get(self::$testpath)->call('system.svn.checkout.php', $args );
		ob_end_clean();
		return $res;
	}

	/**
	 * @large
	 */
	public function testCheckoutVersion() {
		$version = 22;
		$args = array (
			'arNewType' => 'psection.library',
			'arNewFilename' => '{5:id}',
			'nl' => array (
				'name' => 'test lib',
			),
			'en' => array (
				'name' => 'test lib',
			),
			'de' => array (
				'name' => 'test lib',
			)
		);

		self::$testpath = current(ar::get(TESTBASE)->call('system.new.phtml' , $args));
		$this->helperCheckout($version);
		$res = ar::get(self::$testpath)->call('system.svn.info.php');
		$info = current($res);
		$info = array_change_key_case($info, CASE_LOWER);
		$checkrev = $info['revision']??null;
		$this->assertEquals($version,$checkrev);
		$obj = current(ar::get(self::$testpath)->call('system.get.phtml'));
		$this->assertTrue(count($obj->data->config->pinp) > 0);
	}

	public function testbasediff(){
		try {
			$res = ar::get(TESTBASE)->call('system.svn.diff.php');
		} catch (Exception $e) {
			$this->assertNull($e, 'Exception should not happen');
		}
		// we should catch that exception
		$this->assertInstanceOf('ar_error',current($res));
	}

	/**
	 * @large
	 */
	public function testDiff(){
		$args = array (
			'arNewType' => 'psection.library',
			'arNewFilename' => '{5:id}',
			'nl' => array (
				'name' => 'test lib',
			),
			'en' => array (
				'name' => 'test lib',
			),
			'de' => array (
				'name' => 'test lib',
			)
		);

		self::$testpath = current(ar::get(TESTBASE)->call('system.new.phtml' , $args));
		$this->helperCheckout(22);
		$args  = array (
			"template" => 'changed',
			"default"  => 1,
			"type"     => 'pobject',
			"function" => 'test.view.html',
			"language" => 'any',
			"private"  => false,
		);
		$res = current(ar::get(self::$testpath)->call('system.save.layout.phtml' , $args));
		$res = current(ar::get(self::$testpath)->call('system.svn.diff.php'));
		$this->assertIsInt(strpos($res,'+changed'));
		$this->assertNotFalse(strpos($res,'+changed'));

	}

	/**
	 * @large
	 */
	public function testRevert(){
		$args = array (
			'arNewType' => 'psection.library',
			'arNewFilename' => '{5:id}',
			'nl' => array (
				'name' => 'test lib',
			),
			'en' => array (
				'name' => 'test lib',
			),
			'de' => array (
				'name' => 'test lib',
			)
		);

		self::$testpath = current(ar::get(TESTBASE)->call('system.new.phtml' , $args));
		$this->helperCheckout();
		$args  = array (
			"template" => 'changed',
			"default"  => 1,
			"type"     => 'pobject',
			"function" => 'test.view.html',
			"language" => 'any',
			"private"  => false,
		);
		$res = current(ar::get(self::$testpath)->call('system.save.layout.phtml' , $args));
		$res = current(ar::get(self::$testpath)->call('system.svn.diff.php'));
		$this->assertIsInt(strpos($res,'+changed'));
		$this->assertNotFalse(strpos($res,'+changed'));

		// reverting
		ob_start();
		ar::get(self::$testpath)->call('system.svn.revert.php');
		ob_end_clean();
		$res = current(ar::get(self::$testpath)->call('system.svn.diff.php'));
		$this->assertFalse(strpos($res,'+changed'));
		$this->assertNotInternalType('int',strpos($res,'+changed'));
	}

	/**
	 * @large
	 */
	public function testDiffServer(){
		$args = array (
			'arNewType' => 'psection.library',
			'arNewFilename' => '{5:id}',
			'nl' => array (
				'name' => 'test lib',
			),
			'en' => array (
				'name' => 'test lib',
			),
			'de' => array (
				'name' => 'test lib',
			)
		);

		self::$testpath = current(ar::get(TESTBASE)->call('system.new.phtml' , $args));
		$this->helperCheckout(22);
		$res = current(ar::get(self::$testpath)->call('system.svn.diff.php',array('revision' => 23)));
		$this->assertIsInt(strpos($res,'+test:serverdiff'));
		$this->assertNotFalse(strpos($res,'+test:serverdiff'));

		$res = current(ar::get(self::$testpath)->call('system.svn.diff.php',array('revision' => 22)));
		$this->assertEmpty($res);
		$this->assertIsInt(strpos($res,'+test:serverdiff'));
		$this->assertFalse(strpos($res,'+test:serverdiff'));

	}

	public function testUpdate(){
		$args = array (
			'arNewType' => 'psection.library',
			'arNewFilename' => '{5:id}',
			'nl' => array (
				'name' => 'test lib',
			),
			'en' => array (
				'name' => 'test lib',
			),
			'de' => array (
				'name' => 'test lib',
			)
		);

		self::$testpath = current(ar::get(TESTBASE)->call('system.new.phtml' , $args));
		$args = array (
			'type'     => 'pobject',
			'function' => 'test.view.html',
			'language' => 'any'
		);
		$this->helperCheckout(22);
		$res = current(ar::get(self::$testpath)->call('system.get.template.php',$args));
		$this->assertEquals('template:test.view.html',$res);
		// update outputs information, remove it for phpunit
		ob_start();
		ar::get(self::$testpath)->call('system.svn.update.php');
		ob_end_clean();
		$res = current(ar::get(self::$testpath)->call('system.get.template.php',$args));
		$this->assertNotEquals('template:test.view.html',$res);

		// update outputs information, remove it for phpunit
		// update back to 22
		ob_start();
		ar::get(self::$testpath)->call('system.svn.update.php', array('revision' => 22));
		ob_end_clean();
		$res = current(ar::get(self::$testpath)->call('system.get.template.php',$args));
		$this->assertEquals('template:test.view.html',$res);

	}

	/**
	 * @large
	 */
	public function testUpdateConflict(){
		$args = array (
			'arNewType' => 'psection.library',
			'arNewFilename' => '{5:id}',
			'nl' => array (
				'name' => 'test lib',
			),
			'en' => array (
				'name' => 'test lib',
			),
			'de' => array (
				'name' => 'test lib',
			)
		);

		self::$testpath = current(ar::get(TESTBASE)->call('system.new.phtml' , $args));
		$args = array (
			'type'     => 'pobject',
			'function' => 'test.view.html',
			'language' => 'any'
		);
		$this->helperCheckout(22);
		$res = current(ar::get(self::$testpath)->call('system.get.template.php',$args));
		$this->assertEquals('template:test.view.html',$res);

		// create conflict
		$args  = array (
			"template" => 'changed',
			"default"  => 1,
			"type"     => 'pobject',
			"function" => 'test.view.html',
			"language" => 'any',
			"private"  => false,
		);
		$res = current(ar::get(self::$testpath)->call('system.save.layout.phtml' , $args));

		// update outputs information, remove it for phpunit
		ob_start();
		ar::get(self::$testpath)->call('system.svn.update.php', array('revision' => 23));
		ob_end_clean();

		$res = current(ar::get(self::$testpath)->call('system.get.template.php',$args));
		$prep =
"<<<<<<< .mine
changed=======
template:test.view.html
test:serverdiff
>>>>>>> .r23
";
		$this->assertEquals($prep,$res);
	}

/*
resolve
*/
}
?>
