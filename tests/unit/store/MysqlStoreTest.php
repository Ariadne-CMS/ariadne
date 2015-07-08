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
		if( $store_config["dbms"] !== 'mysql' ) {
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
}
?>
