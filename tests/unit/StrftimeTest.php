<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2).'/lib/ar.php';
require_once dirname(__DIR__, 2).'/lib/modules/mod_pinp.phtml';

class StrftimeTest extends TestCase
{
	private function runCompiledCall($function, $arguments, $variables)
	{
		$scope = $this->getMockForAbstractClass(ariadne_object::class);
		$object = new ar_core_pinpSandbox($scope);
		foreach ($variables as $name => $value) {
			$object->$name = $value;
		}
		$compiler = new pinp('', 'object->', '$object->_');
		$compiled = $compiler->compile('<pinp>$result = '.$function.'('.$arguments.');</pinp>');
		$this->assertNull($compiler->error);
		$this->assertStringContainsString('$object->strftime(', strtolower($compiled));
		eval('?>'.$compiled);
		return $object->result;
	}

	/** @dataProvider descriptionFormats */
	public function testCompiledCallsUseCompatibilityWrapper($format, $expected)
	{
		foreach (array('strftime', 'STRFTIME') as $function) {
			$result = $this->runCompiledCall($function, '$format, $timestamp', array(
				'format' => $format,
				'timestamp' => gmmktime(14, 35, 42, 10, 27, 2026)
			));
			$this->assertSame($expected, $result);
		}
	}

	/** @dataProvider descriptionFormats */
	public function testLiteralTextIsNotInterpretedAsDateCodes($format, $expected)
	{
		$object = $this->getMockForAbstractClass(ariadne_object::class);
		$timestamp = gmmktime(14, 35, 42, 10, 27, 2026);
		$this->assertSame($expected, $object->_strftime($format, $timestamp));
	}

	public static function descriptionFormats()
	{
		return array(
			'creation description' => array('%Y-%m-%d %H:%M: Object created by Example', '2026-10-27 14:35: Object created by Example'),
			'text before date' => array('Created by Example: %Y-%m-%d %H:%M', 'Created by Example: 2026-10-27 14:35'),
			'plain text' => array('Created by Example', 'Created by Example'),
			'date only' => array('%Y-%m-%d %H:%M:%S', '2026-10-27 14:35:42'),
			'escaped percent' => array('%%Y %% %Y %%%m', '%Y % 2026 %10'),
			'backslashes and unicode' => array('C:\\Example\\ %Y — créé', 'C:\\Example\\ 2026 — créé'),
			'unknown directive' => array('%Q %Y trailing %', '%Q 2026 trailing %'),
			'empty format' => array('', ''),
			'weekday and timezone' => array('%a %b %d %Y %H:%M:%S %z', 'Tue Oct 27 2026 14:35:42 +0000'),
		);
	}

	public function testOmittedTimestampUsesCurrentTime()
	{
		$before = gmdate('Y-m-d H:i');
		$description = $this->runCompiledCall('strftime', '$format', array(
			'format' => 'Created by Example: %Y-%m-%d %H:%M'
		));
		$after = gmdate('Y-m-d H:i');
		$this->assertContains($description, array('Created by Example: '.$before, 'Created by Example: '.$after));
	}
}
