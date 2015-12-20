<?php

class StorePropertyTest extends AriadneBaseTest
{
	public function setUp()
	{
		$this->initAriadne();
	}

	public function testLoadproperties() {
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

	public function testDelAddpropertie() {
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

	public function testAllScopedProperties(){
		global $store;

		$testpath = TESTBASE.'/projects/demo/demo/';
		$demoid = $store->exists($store->make_path('/',$testpath));

		// should load all properties
		$prop = $store->load_properties($demoid, "", "%");
		$this->assertArrayHasKey('owner',$prop,"Properties doesn't contain an owner");
		$this->assertArrayHasKey('text',$prop,"Properties doesn't contain a text");
		$this->assertArrayHasKey('name',$prop,"Properties doesn't contain a name");
		$this->assertArrayHasKey('time',$prop,"Properties doesn't contain a time");

		$entry = current($prop['name']);
		$this->assertEquals(2, strlen($entry['nls']), "nls should only be 2 chars");
	}

	public function testaddPropertyScoped() {
		global $store;

		$testpath = TESTBASE.'/projects/demo/demo/';
		$demoid = $store->exists($store->make_path('/',$testpath));

		$origProp = $store->load_property($demoid,'name',"","unittest1");
		$store->add_property($demoid,'name',array ( 'value' => "unittest1"),"unittest1");
		$store->add_property($demoid,'name',array ( 'value' => "unittest2"),"unittest2");
		$prop1 = $store->load_property($demoid,'name',"","unittest1");
		$prop2 = $store->load_property($demoid,'name',"","unittest2");
		$allProp = $store->load_property($demoid,'name',"","%");
		$scopelessProp = $store->load_property($demoid,'name');

		$this->assertNotEquals($origProp,$prop1,'Name property should have been added');
		$this->assertNotEquals($prop1,$prop2,'Both properties should container other information');
		$this->assertArrayHasKey('value',$prop1[0],"Properties doesn't contain a name");
		$this->assertArrayHasKey('nls',$prop1[0],"Properties doesn't contain a name");
		$this->assertArrayHasKey('scope',$prop1[0],"Properties doesn't contain a name");
		$this->assertArrayHasKey('value',$prop2[0],"Properties doesn't contain a name");
		$this->assertArrayHasKey('nls',$prop2[0],"Properties doesn't contain a name");
		$this->assertArrayHasKey('scope',$prop2[0],"Properties doesn't contain a name");
		$this->assertNotEquals($prop1[0]['value'], $prop2[0]['value']);

		$found = 0;
		foreach($allProp as $prop){
			if($prop['value'] == "unittest1") {
				// trigger assert
				$found++;
			}
		}
		$this->assertEquals($found,1);

		$found = 0;
		foreach($scopelessProp as $prop){
			if($prop['value'] == "unittest1") {
				// trigger assert
				$found++;
			}
		}
		$this->assertEquals($found,0);

	}
}
?>
