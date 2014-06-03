<?php

/**
 * @backupGlobals disabled
 */
class StorePropertyTest extends PHPUnit_Framework_TestCase
{
	protected static $demoid;
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
		self::$demoid = $store->exists('/projects/demo/demo/');

	}

	function testLoadproperties() {
		global $store;
		$prop = $store->load_properties(self::$demoid);
		$this->assertArrayHasKey('owner',$prop,"Properties doesn't contain an owner");
		$this->assertArrayHasKey('text',$prop,"Properties doesn't contain a text");
		$this->assertArrayHasKey('name',$prop,"Properties doesn't contain a name");
		$this->assertArrayHasKey('time',$prop,"Properties doesn't contain a time");

		$entry = current($prop['name']);
	}

	function testDelAddpropertie() {
		global $store;

		$origProp = $store->load_property(self::$demoid,'name');
		$store->del_property(self::$demoid,'name');
		$newProp = $store->load_property(self::$demoid,'name');
		$store->add_property(self::$demoid,'name',current($origProp));
		$resaveProp = $store->load_property(self::$demoid,'name');

		$this->assertNotEquals($origProp,$newProp,'Name propertie should be deleted now');
		$this->assertEquals($origProp,$resaveProp,'Name property should be the same');
	}
}
?>