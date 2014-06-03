<?php

/**
 * @backupGlobals disabled
 */
class AdminUserTest extends PHPUnit_Framework_TestCase
{

	public static function setUpBeforeClass()
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