<?php

class AdminUserTest extends AriadneBaseTest
{

	public function setUp(): void
	{
		$this->initAriadne();
	}

	public function testAdminIsAvailable() {
		global $AR;
		$objects = ar::get('/system/users/admin/')->call('system.get.phtml');
		$obj = array_pop($objects);
		$this->assertEquals('/system/users/admin/',$obj->path);
	}

	public function testAdminHasAllRights() {
		$objects = ar::get('/')->call('system.get.phtml');
		$obj = array_pop($objects);
		$res = $obj->CheckSilent('randomstuf'.md5(time()));
		$this->assertEquals($res,1);
	}

}
?>
