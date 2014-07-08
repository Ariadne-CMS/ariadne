<?php

class StorePropertyTest extends PHPUnit_Framework_TestCase
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

	function testLoadproperties() {
		global $store;

		$testpath = TESTBASE.'/projects/demo/demo/';
		$demoid = $store->exists($store->make_path('/',$testpath));

		$prop = $store->load_properties($demoid);
		$this->assertArrayHasKey('owner',$prop,"Properties doesn't contain an owner");
		$this->assertArrayHasKey('text',$prop,"Properties doesn't contain a text");
		$this->assertArrayHasKey('name',$prop,"Properties doesn't contain a name");
		$this->assertArrayHasKey('time',$prop,"Properties doesn't contain a time");

		$entry = current($prop['name']);
		$this->assertEquals(2, strlen($entry['nls']), "nls should only be 2 chars");
	}

	function testDelAddpropertie() {
		global $store;

		$testpath = TESTBASE.'/projects/demo/demo/';
		$demoid = $store->exists($store->make_path('/',$testpath));

		$origProp = $store->load_property($demoid,'name');
		$store->del_property($demoid,'name');
		$newProp = $store->load_property($demoid,'name');
		$store->add_property($demoid,'name',current($origProp));
		$resaveProp = $store->load_property($demoid,'name');

		$this->assertNotEquals($origProp,$newProp,'Name property should be deleted now');
		$this->assertEquals($origProp,$resaveProp,'Name property should be the same');
	}
}
?>