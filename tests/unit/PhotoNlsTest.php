<?php

class PhotoNlsTest extends PHPUnit_Framework_TestCase
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

		$obj =current(ar::get(TESTBASE.'/image-nls/image-nls/')->call('system.get.phtml'));
		$defaultnls = $obj->data->nls->default;

		$one = $obj->getExif(false,true,false, $defaultnls);
		$two = $obj->getExif();
		unset($one['FILE']['FileName']);
		unset($two['FILE']['FileName']);
		$this->assertEquals($defaultnls,$one['IFD0']['ImageDescription']);
		$this->assertEquals($one,$two);
	}

	public function testObjectAllNLS() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/image-nls/image-nls/')->call('system.get.phtml'));

		foreach($obj->data->nls->list as $nls => $language) {
			$content = $obj->getExif(false,true,false, $nls);
			$content = trim($content['IFD0']['ImageDescription']);
			$this->assertEquals($nls , $content);
		}
	}

}
?>