<?php

class svnTest extends AriadneBaseTest
{
	private static $repo = 'https://svn.muze.nl/svn/test/testlibrary/';
	private static $testpath;

	public function setUp()
	{
		$this->initAriadne();
	}

	public static function setUpBeforeClass()
	{
		self::$testpath = current(ar::get(TESTBASE)->call('system.new.phtml' , $args));
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
		$checkrev = array_change_key_case($info, CASE_LOWER)['revision'];
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

	/*
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
		$this->helperCheckout();
		$args  = array (
		);
		self::$testpath = current(ar::get(TESTBASE)->call('system.save.layout.phtml' , $args));

	}
		*/

/*
revert
resolve
diff
server diff HEAD
server diff version
update PREV
update HEAD
update non-conflict
update conflict
*/
}
?>
