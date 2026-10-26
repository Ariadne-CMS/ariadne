<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4).'/lib/ar.php';
require_once dirname(__DIR__, 4).'/lib/modules/mod_pinp.phtml';

class DbReferenceTest extends TestCase
{
	protected function setUp(): void
	{
		if (!extension_loaded('pdo_sqlite')) $this->markTestSkipped('PDO SQLite required.');
	}

	public function testNativeCallsPreserveInputAndOutputBindings()
	{
		$db = ar_connect_db::client('sqlite::memory:');
		$stmt = $db->prepare('SELECT :value AS value');
		$value = 1;
		$this->assertTrue($stmt->bindParam(':value', $value, PDO::PARAM_INT));
		$this->assertTrue($stmt->bindColumn('value', $result, PDO::PARAM_INT));
		$value = 42;
		$stmt->execute();
		$stmt->fetch(PDO::FETCH_BOUND);
		$this->assertSame(42, $result);
		$this->assertSame(array(), get_object_vars($db->__unwrap()));
		$this->assertArrayNotHasKey('row', get_object_vars($stmt->__unwrap()));
		$this->assertArrayNotHasKey('cursor', get_object_vars($stmt->__unwrap()));
	}

	/** @dataProvider pinpBindings */
	public function testPinpBindings($inputMethod, $outputMethod, $target, $initial)
	{
		$object = new stdClass();
		$object->statement = ar_connect_db::client('sqlite::memory:')->prepare('SELECT :value AS value');
		$compiler = new pinp('', 'object->', '$object->');
		$source = $initial.'$value = 1; $statement->'.$inputMethod.'(":value", $value, '.PDO::PARAM_INT.');'
			.'$statement->'.$outputMethod.'("value", '.$target.', '.PDO::PARAM_INT.');'
			.'$value = 42; $statement->execute(); $statement->fetch('.PDO::FETCH_BOUND.'); $result = '.$target.';';
		$compiled = $compiler->compile('<pinp>'.$source.'</pinp>');
		$this->assertNull($compiler->error);
		eval('?>'.$compiled);
		$this->assertSame(42, $object->result);
	}

	public static function pinpBindings()
	{
		return array(
			array('bindParam', 'bindColumn', '$output', ''),
			array('_bindParam', '_bindColumn', '$output["value"]', '$output = array();'),
			array('BINDPARAM', 'BINDCOLUMN', '$output[$key]', '$output = array(); $key = "value";'),
			array('bindParam', 'bindColumn', '$output->value', '$output = json_decode("{}");')
		);
	}

	public function testIterationKeepsStateOnTheWrapper()
	{
		$db = ar_connect_db::client('sqlite::memory:');
		$stmt = $db->query('SELECT 10 AS value UNION ALL SELECT 20');
		$values = array();
		foreach ($stmt as $key => $row) $values[$key] = $row['value'];
		$this->assertSame(array(10, 20), $values);
	}
}
