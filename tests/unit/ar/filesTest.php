<?php

class arFilesTest extends PHPUnit_Framework_TestCase
{

	function setUp()
	{
		global $ariadne,$store_config,$store,$AR;
		/* instantiate the store */
		$inst_store = $store_config["dbms"]."store";
		$store = new $inst_store($root,$store_config);

		/* now load a user (admin in this case)*/
		$login = "admin";
		$query = "object.implements = 'puser' and login.value='$login'";
		$AR->user = current($store->call('system.get.phtml', '', $store->find('/system/users/', $query)));
		
		//FIXME: make a copy of the FilesNls.ax for this test as well.
	}

	public function testSaveAndGet() {
		global $AR;

		$me = $this;
		ar::context()->callAtPath(TESTBASE.'file-nls/', function() use ($me) {
			$content = "file contents";
			$result = ar_files::save('test',$content,'nl');
			$me->assertEquals($result, strlen($content));

			$result = ar_files::get('test','nl');
			if ( !ar_error::isError($result) ) {
				$result = $result->getContents();
			}
			$me->assertEquals($content, $result);
		} );
	}

	public function testNls() {
		global $AR;

		$me = $this;
		ar::context()->callAtPath(TESTBASE.'file-nls/', function() use ($me) {
			$result = ar_files::ls();
			$me->assertEquals(count($result), 1);
			$me->assertEquals($result[0]['name'], 'test');
			$me->assertEquals($result[0]['nls'], 'nl');

		} );
	}

	public function testDelete() {
		global $AR;

		$me = $this;
		ar::context()->callAtPath(TESTBASE.'file-nls/', function() use ($me) {
			ar_files::delete('test','nl');
			$result = ar_files::ls();
			$me->assertEquals(count($result), 0);
		} );
	}


}
?>