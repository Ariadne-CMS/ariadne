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

	public function testDefaultNLS() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));
		$defaultnls = $obj->data->nls->default;

		$one = trim($obj->getFile('file',$defaultnls));
		$two = trim($obj->getFile());
		$this->assertEquals('taal '.$defaultnls,$one);
		$this->assertEquals($one,$two);
	}

	public function testAllNLS() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));

		foreach($obj->data->nls->list as $nls => $language) {
			$content = $obj->getFile('file',$nls);
			$content = trim($content);
			$this->assertEquals('taal '.$nls , $content);
		}
	}

	public function testAllNLSPlain() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));

		foreach($obj->data->nls->list as $nls => $language) {
			$content = $obj->getPlainText('file',$nls);
			$content = trim($content);
			$this->assertEquals('taal '.$nls , $content);
		}
	}

	public function testAllNLSShow() {
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

	public function testunavailableNls() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));
		$content = $obj->getFile('file','zz');
		$content = trim($content);
		$this->assertEquals('taal '.$obj->data->nls->default , $content);
	}

	public function testUnavailableNlsPlain() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));
		$nls = $obj->data->nls->default;
		$content = $obj->getPlainText('file','zz');
		$content = trim($content);
		$this->assertEquals('taal '.$nls , $content);
	}

	public function testunavailableNlsChangeDefault() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-nls/')->call('system.get.phtml'));
		$nls = $obj->data->nls->default;
		$obj->data->nls->default = 'de';
		$content = $obj->getFile('file','zz');
		$content = trim($content);
		$this->assertNotEquals('taal '.$nls , $content);
	}

	public function testAllNLSPlainHTML() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-html/')->call('system.get.phtml'));

		foreach($obj->data->nls->list as $nls => $language) {
			$content = $obj->getPlainText('file',$nls);
			$content = trim($content);
			$this->assertEquals($nls , $content);
		}
	}
	public function testAllNLSPlainPDF() {
		global $AR;

		$obj =current(ar::get(TESTBASE.'/file-nls/file-pdf/')->call('system.get.phtml'));

		foreach($obj->data->nls->list as $nls => $language) {
			$content = $obj->getPlainText('file',$nls);
			$content = trim($content);
			$this->assertEquals($nls , $content);
		}
	}

	public function testSaveExistingFile() {
		global $AR,$ARCurrent;
		$content = 'testfrmlfrop';
		$obj  = current(ar::get(TESTBASE.'/file-nls/file-nls/' )->call('system.get.phtml'));

		$res = $obj->SaveFile($content ,'text/plain', 'newfile', $obj->nls);
		$this->assertEquals(strlen($content), $res);

		$res = $obj->GetFile('newfile', $obj->nls);
		$this->assertEquals($content, $res);
	}

/*
	TODO:
		- save file for new object
		- parse file
		- showfile
			- mimetype
			- headers
		- download file
			- file
			- mimetype
			- headers
*/
}
?>