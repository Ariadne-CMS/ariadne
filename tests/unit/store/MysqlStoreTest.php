<?php

class MysqlStoreTest extends AriadneBaseTest
{
	public static function setUpBeforeClass()
	{
		self::initAriadne();
		parent::setUpBeforeClass();
	}

	protected function setUp()
	{
		global $store_config;
		if( strpos($store_config["dbms"],'mysql') !== 0 ){
			$this->marktestskipped(
				'Skipping mysql tests, current config uses: '.$store_config["dbms"]
			);
		}

	}

	public function test_format_for_fti(){
		global $store;
		$prep = 'abc_c3_bc_5f_2d';
		$res = $store->format_for_fti('abcü_-');
		$this->assertEquals($prep,$res);
	}

	public function  test(){
		global $store;
		$res = $store->find('/','object.id = 1');
		$row = $res['list']->fetch_array(MYSQLI_ASSOC);
		$this->assertArrayHasKey('id', $row);
		$this->assertArrayHasKey('path', $row);
		$this->assertArrayHasKey('parent', $row);
		$this->assertArrayHasKey('lastchanged', $row);
		$this->assertArrayHasKey('priority', $row);
		$this->assertArrayHasKey('type', $row);
		$this->assertArrayHasKey('vtype', $row);
	}
}
?>
