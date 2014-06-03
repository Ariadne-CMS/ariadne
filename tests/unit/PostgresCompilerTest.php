<?php

class PostgresCompilerTest extends PHPUnit_Framework_TestCase
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

	protected function setUp()
	{
		global $store_config;
		if( $store_config["dbms"] !== 'postgresql' ) {
			$this->marktestskipped(
				'Skipping postgresql tests, current config uses: '.$store_config["dbms"]
			);
		}
	}

	function testBaseCompile() {
		global $store;
		$compiler = 'postgresql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/',"owner.value = 'admin'");
		$prepared = "select distinct(store_nodes.path), store_nodes.parent, store_nodes.priority,  store_objects.id, store_objects.type, store_objects.object, date_part('epoch', store_objects.lastchanged) as lastchanged, store_objects.vtype  from store_prop_owner, store_nodes, store_objects  where store_nodes.object=store_objects.id  and store_nodes.path like '/%'  and (   store_prop_owner.object = store_objects.id and store_prop_owner.AR_value  =  'admin'   )  order by store_nodes.parent ASC, store_nodes.priority DESC, store_nodes.path ASC   offset 0 limit 100  ";
		$this->assertEquals($prepared, $res);

	}

	function testQuoteCompile() {
		global $store;
		$compiler = 'postgresql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/',"owner.value = 'ad\'min'");
		$prepared = "select distinct(store_nodes.path), store_nodes.parent, store_nodes.priority,  store_objects.id, store_objects.type, store_objects.object, date_part('epoch', store_objects.lastchanged) as lastchanged, store_objects.vtype  from store_prop_owner, store_nodes, store_objects  where store_nodes.object=store_objects.id  and store_nodes.path like '/%'  and (   store_prop_owner.object = store_objects.id and store_prop_owner.AR_value  =  'ad''min'   )  order by store_nodes.parent ASC, store_nodes.priority DESC, store_nodes.path ASC   offset 0 limit 100  ";
		$this->assertEquals($prepared, $res);
	}
}
?>