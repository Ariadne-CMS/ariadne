<?php

class arFilesTest extends AriadneBaseTest
{

	function setUp()
	{
		$this->initAriadne();
	}

	public function testSaveAndGet() {
		global $AR;

		$me = $this;
		ar::context()->callAtPath(TESTBASE.'file-nls/', function() use ($me) {
			$content = "file contents";
			$result = ar_store_files::save('test',$content,'nl');
			$me->assertEquals($result, strlen($content));

			$result = ar_store_files::get('test','nl');
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
			$result = ar_store_files::ls();
			$me->assertEquals(count($result), 1);
			$me->assertEquals($result[0]['name'], 'test');
			$me->assertEquals($result[0]['nls'], 'nl');

		} );
	}

	public function testDelete() {
		global $AR;

		$me = $this;
		ar::context()->callAtPath(TESTBASE.'file-nls/', function() use ($me) {
			ar_store_files::delete('test','nl');
			$result = ar_store_files::ls();
			$me->assertEquals(count($result), 0);
		} );
	}

	/* TODO:
		- temp file with and without contents
		- touch
	*/

}
?>
