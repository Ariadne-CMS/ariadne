<?php

class FileNlsTest extends PHPUnit_Framework_TestCase
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

	public function testObjectAllNLSPlain() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));

		foreach($obj->data->nls->list as $nls => $language) {
			$content = $obj->getPlainText('file',$nls);
			$content = trim($content);
			$this->assertEquals('taal '.$nls , $content);
		}
	}

	public function testObjectAllNLSShow() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));
		foreach($obj->data->nls->list as $nls => $language) {
			ob_start();
			$content = $obj->ShowFile('file',$nls);
			$content  = ob_get_contents();
			ob_end_clean();
			$content = trim($content);
			$this->assertEquals('taal '.$nls , $content);
		}
	}

	/*
	FIXME: disabled for now, testcase triggers a transition bug
	should return default language, but while the pfile is not yet to work without '_file' for default nls

	public function testObjectunavailableNls() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));
		$content = $obj->getFile('file','zz');
		$content = trim($content);
		$this->assertEquals('taal '.$obj->data->nls->default , $content);
	}
	*/

	public function testObjectUnavailableNlsPlain() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));
		$nls = $obj->data->nls->default;
		$content = $obj->getPlainText('file','zz');
		$content = trim($content);
		$this->assertEquals('taal '.$nls , $content);
	}

	public function testObjectunavailableNlsChangeDefault() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));
		$nls = $obj->data->nls->default;
		$obj->data->nls->default = 'de';
		$content = $obj->getFile('file','zz');
		$content = trim($content);
		$this->assertNotEquals('taal '.$nls , $content);
	}

}
?>