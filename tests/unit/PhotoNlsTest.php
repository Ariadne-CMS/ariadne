<?php

class PhotoNlsTest extends AriadneBaseTest
{

	public function setUp()
	{
		$this->initAriadne();
	}

	public function testObjectDefaultNLS() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/image-nls/image-nls/')->call('system.get.phtml'));
		$defaultnls = $obj->data->nls->default;

		$one = $obj->getExif(false,true,false, $defaultnls);
		$two = $obj->getExif();
		unset($one['FILE']['FileName']);
		unset($two['FILE']['FileName']);
		unset($one['FILE']['FileDateTime']);
		unset($two['FILE']['FileDateTime']);
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
