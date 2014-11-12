<?php

class AdminUserTest extends AriadneBaseTest
{

	public function setUp()
	{
		$this->initAriadne();
	}

	public function testAdminIsAvailable() {
		global $AR;
		$obj = array_pop(ar::get('/system/users/admin/')->call('system.get.phtml'));
		$this->assertEquals('/system/users/admin/',$obj->path);
	}

	public function testAdminHasAllRights() {
		$obj = array_pop(ar::get('/')->call('system.get.phtml'));
		$res = $obj->CheckSilent('randomstuf'.md5(time()));
		$this->assertEquals($res,1);
	}

}
?>
