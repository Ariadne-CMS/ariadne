<?php

class FileNlsText extends PHPUnit_Framework_TestCase
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

	}

	public function testObjectDefaultNLS() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));
		$defaultnls = $obj->data->nls->default;

		$one = trim($obj->getFile('file',$defaultnls));
		$two = trim($obj->getFile());
		$this->assertEquals('taal '.$defaultnls,$one);
		$this->assertEquals($one,$two);
	}

	public function testObjectAllNLS() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));

		foreach($obj->data->nls->list as $nls => $language) {
			$content = $obj->getFile('file',$nls);
			$content = trim($content);
			$this->assertEquals('taal '.$nls , $content);
		}
	}

}
?>