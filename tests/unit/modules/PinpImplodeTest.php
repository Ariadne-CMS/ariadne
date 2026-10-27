<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/ar.php';
require_once dirname(__DIR__, 3).'/lib/modules/mod_pinp.phtml';

class PinpImplodeTest extends TestCase
{
	/** @dataProvider implodeCalls */
	public function testCompiledCallsUseCompatibilityWrapper($call, $items, $expected)
	{
		$scope = $this->getMockForAbstractClass(ariadne_object::class);
		$object = new ar_core_pinpSandbox($scope);
		$object->items = $items;
		$compiler = new pinp('', 'object->', '$object->_');
		$compiled = $compiler->compile('<pinp>$result = '.$call.';</pinp>');
		$this->assertNull($compiler->error);
		$this->assertStringContainsString('$object->implode(', strtolower($compiled));
		eval('?>'.$compiled);
		$this->assertSame($expected, $object->result);
	}

	public static function implodeCalls()
	{
		return array(
			'legacy order' => array('implode($items, ", ")', array('one', 'two'), 'one, two'),
			'modern order' => array('implode(", ", $items)', array('one', 'two'), 'one, two'),
			'single argument' => array('implode($items)', array('one', 'two'), 'onetwo'),
			'empty legacy array' => array('implode($items, ", ")', array(), ''),
			'empty modern array' => array('implode(", ", $items)', array(), ''),
			'empty single argument' => array('implode($items)', array(), ''),
			'associative array' => array('implode($items, " ")', array('first' => 'one', 'second' => 'two'), 'one two'),
			'case insensitive' => array('IMPLODE($items, " ")', array('one', 'two'), 'one two')
		);
	}
}
