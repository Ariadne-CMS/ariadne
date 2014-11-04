<?php

class StoreTest extends PHPUnit_Framework_TestCase
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

	/*
	 * TODO:
		move
		move {id}
		move under self
		move to existing
	 */

	function testExists(){
		global $store;
		$testpath = TESTBASE.'/projects/demo/demo/';
		$testpath = $store->make_path('/',$testpath);
		$demoid = $store->exists($testpath);
		$this->assertTrue(is_int($demoid));
	}

	function testmakepath(){
		global $store;
		$partial = './frop';
		$res = $store->make_path(TESTBASE,$partial);
		$this->assertEquals(TESTBASE . 'frop/', $res);
	}

	function testNotExists(){
		global $store;
		$testpath = TESTBASE.'/thisonedoesnotexists/';
		$testpath = $store->make_path('/',$testpath);
		$demoid = $store->exists($testpath);
		$this->assertNull($demoid);
	}

	function testNextID(){
		global $store;
		$testpath = TESTBASE.'/storeTest-nextID/';
		$testpath = $store->make_path('/',$testpath);

		self::testNew('storeTest-nextID');

		$id = $store->get_nextid($testpath);
		$this->assertEquals('00001', $id);

		// create object with ID
		self::testNew('storeTest-nextID/'.$id);

		$id = $store->get_nextid($testpath,'{5:id}');
		$this->assertEquals('00002', $id);
	}

	function testNew($path="storeTest-new"){
		global $store;
		$testpath = TESTBASE.'/'.$path.'/';
		$testpath = $store->make_path('/',$testpath);

		// create object
		$ret = $store->save($store->make_path('/',$testpath),'pobject',new object());
		$this->assertEquals($testpath, $ret);
		return $ret;
	}

	function testNewID(){
		global $store;
		$mask = 'storeTest-{5:id}-new';
		$testpath = TESTBASE.'/'.$mask.'/';
		$testpath = $store->make_path('/',$testpath);

		$id = $store->get_nextid(TESTBASE, $mask);

		// create object
		$ret = $store->save($store->make_path('/',$testpath),'pobject',new object());
		$this->assertNotEquals($testpath, $ret);

		$prep = str_replace ( '{5:id}', $id, $testpath);
		$this->assertEquals($prep, $ret);

		// secondairy
		$id = $store->get_nextid(TESTBASE, $mask);

		// create object
		$ret2 = $store->save($store->make_path('/',$testpath),'pobject',new object());
		$this->assertNotEquals($testpath, $ret2);
		$this->assertNotEquals($ret, $ret2);

		$prep = str_replace ( '{5:id}', $id, $testpath);
		$this->assertEquals($prep, $ret2);
	}

	function testSave() {
		global $store;
		$path = self::testNew($path="storeTest-save");

		$dbobj = $store->get($path);
		$row1 = $store->call('system.get.phtml','',$dbobj);
		$this->assertNotEquals('2', $row1->data->test);

		$data = new object();
		$data->test = "2";
		$ret = $store->save($path,'pobject',$data);
		$this->assertEquals($path, $ret);

		$dbobj = $store->get($path);
		$row2 = $store->call('system.get.phtml','',$dbobj);
		$this->assertNotEquals('2', $row2->data->test);
	}

	function testDelete() {
		global $store;
		$path = self::testNew($path="storeTest-delete");
		$id = $store->exists($path);
		$this->assertTrue(is_int($id));
		$ret = $store->delete($path);
		$this->assertTrue((bool)$ret);
		$id = $store->exists($path);
		$this->assertNull($id);
	}

}
?>