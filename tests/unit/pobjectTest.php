<?php

class pobjectTest extends AriadneBaseTest
{

	public function setUp()
	{
		$this->initAriadne();
	}

	public function testParents() {
		global $AR;

		$obj = current(
			ar::get(TESTBASE.'projects/demo/demo/images/')->call('system.get.phtml')
		);
		$parents = $obj->parents('','system.get.path.phtml');
		$this->assertTrue(in_array(TESTBASE.'projects/demo/demo/images/',$parents));

		$parents = $obj->parents(TESTBASE.'projects/demo/','system.get.path.phtml');
		// $top of the test object is TESTBASE.'/projects/demo/demo/
		// so the request path is 'above' the $top
		$this->assertEmpty($parents);

		$parents = $obj->parents(TESTBASE.'projects/demo/','system.get.path.phtml',array(),TESTBASE.'projects/demo/');
		$this->assertCount(1,$parents);

		$parents = $obj->parents('','system.get.path.phtml',array(),TESTBASE);
		$this->assertCount(5,$parents);

		$parents = $obj->parents(TESTBASE.'projects/','system.get.path.phtml',array(),TESTBASE);
		$this->assertCount(2,$parents);

		$obj2 = current(
			ar::get(TESTBASE)->call('system.get.phtml')
		);
		$parents2 = $obj2->parents('projects/','system.get.path.phtml',array(),TESTBASE);
		$this->assertCount(2,$parents2);
		$this->assertEquals($parents,$parents2);
		$prep = array (
			TESTBASE,
			TESTBASE.'projects/',
		);
		$this->assertEquals($prep,$parents);

		
		
	}
}

