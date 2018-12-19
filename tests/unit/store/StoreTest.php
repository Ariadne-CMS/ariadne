<?php

class StoreTest extends AriadneBaseTest
{
	public function setUp()
	{
		$this->initAriadne();
	}

	/*
	 * TODO:
		move
		move {id}
		move under self
		move to existing
	 */

	public function testExists(){
		global $store;
		$testpath = TESTBASE.'/projects/demo/demo/';
		$testpath = $store->make_path('/',$testpath);
		$demoid = $store->exists($testpath);
		$this->assertTrue(is_int($demoid));
	}

	public function testmakepath(){
		global $store;
		$partial = './frop';
		$res = $store->make_path(TESTBASE,$partial);
		$this->assertEquals(TESTBASE . 'frop/', $res);
	}

	public function testNotExists(){
		global $store;
		$testpath = TESTBASE.'/thisonedoesnotexists/';
		$testpath = $store->make_path('/',$testpath);
		$demoid = $store->exists($testpath);
		$this->assertNull($demoid);
	}

	public function testNextID(){
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

	public function testNew($path="storeTest-new"){
		global $store;
		$testpath = TESTBASE.'/'.$path.'/';
		$testpath = $store->make_path('/',$testpath);

		// create object
		$ret = $store->save($store->make_path('/',$testpath),'pobject',new baseObject());
		$this->assertEquals($testpath, $ret);
		return $ret;
	}

	public function testNewID(){
		global $store;
		$mask = 'storeTest-{5:id}-new';
		$testpath = TESTBASE.'/'.$mask.'/';
		$testpath = $store->make_path('/',$testpath);

		$id = $store->get_nextid(TESTBASE, $mask);

		// create object
		$ret = $store->save($store->make_path('/',$testpath),'pobject',new baseObject());
		$this->assertNotEquals($testpath, $ret);

		$prep = str_replace ( '{5:id}', $id, $testpath);
		$this->assertEquals($prep, $ret);

		// secondairy
		$id = $store->get_nextid(TESTBASE, $mask);

		// create object
		$ret2 = $store->save($store->make_path('/',$testpath),'pobject',new baseObject());
		$this->assertNotEquals($testpath, $ret2);
		$this->assertNotEquals($ret, $ret2);

		$prep = str_replace ( '{5:id}', $id, $testpath);
		$this->assertEquals($prep, $ret2);
	}

	public function testSave() {
		global $store;
		$path = self::testNew($path="storeTest-save");

		$dbobj = $store->get($path);
		$row1 = $store->call('system.get.phtml','',$dbobj);
		$this->assertNotEquals('2', $row1->data->test);

		$data = new baseObject();
		$data->test = "2";
		$ret = $store->save($path,'pobject',$data);
		$this->assertEquals($path, $ret);

		$dbobj = $store->get($path);
		$row2 = $store->call('system.get.phtml','',$dbobj);
		$this->assertNotEquals('2', $row2->data->test);
	}

	public function testDelete() {
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
