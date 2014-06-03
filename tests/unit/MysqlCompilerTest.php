<?php

class MysqlCompilerTest extends PHPUnit_Framework_TestCase
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
		if( $store_config["dbms"] !== 'mysql' ) {
			$this->marktestskipped(
				'Skipping mysql tests, current config uses: '.$store_config["dbms"]
			);
		}
	}

	function testBaseCompile() {
		global $store;
		$compiler = 'mysql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/',"owner.value = 'admin'");
		$prepared["select_query"]=
		     "select distinct(store_nodes.path), store_objects.id, store_nodes.parent, store_nodes.priority, store_objects.type,  store_objects.object, UNIX_TIMESTAMP(store_objects.lastchanged) as lastchanged, store_objects.vtype from (store_prop_owner, store_nodes, store_objects)   where store_nodes.object=store_objects.id  and store_prop_owner.object=store_objects.id  and store_nodes.path like '/%'  and (   store_prop_owner.AR_value  = 'admin'  )   order by store_nodes.parent ASC, store_nodes.priority DESC, store_nodes.path ASC   limit 0, 100  ";
		$prepared["count_query"]=
				  "select count(distinct(store_nodes.path)) as count from store_prop_owner, store_nodes, store_objects  where store_nodes.object=store_objects.id  and store_prop_owner.object=store_objects.id  and store_nodes.path like '/%'  and (   store_prop_owner.AR_value  = 'admin'  ) ";
		$this->assertEquals($prepared, $res);

	}

	function testQuoteCompile() {
		global $store;
		$compiler = 'mysql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/',"owner.value = 'ad\'min'");
		$prepared["select_query"] =
					 "select distinct(store_nodes.path), store_objects.id, store_nodes.parent, store_nodes.priority, store_objects.type,  store_objects.object, UNIX_TIMESTAMP(store_objects.lastchanged) as lastchanged, store_objects.vtype from (store_prop_owner, store_nodes, store_objects)   where store_nodes.object=store_objects.id  and store_prop_owner.object=store_objects.id  and store_nodes.path like '/%'  and (   store_prop_owner.AR_value  = 'ad\'min'  )   order by store_nodes.parent ASC, store_nodes.priority DESC, store_nodes.path ASC   limit 0, 100  ";
		$prepared["count_query"] =
						"select count(distinct(store_nodes.path)) as count from store_prop_owner, store_nodes, store_objects  where store_nodes.object=store_objects.id  and store_prop_owner.object=store_objects.id  and store_nodes.path like '/%'  and (   store_prop_owner.AR_value  = 'ad\'min'  ) ";
		$this->assertEquals($prepared, $res);
	}
}
?>