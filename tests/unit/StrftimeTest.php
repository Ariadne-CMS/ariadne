<?php

use PHPUnit\Framework\TestCase;

class StrftimeTest extends TestCase
{
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
		$object = $this->getMockForAbstractClass(ariadne_object::class);
		$before = gmdate('Y-m-d H:i');
		$description = $object->_strftime('Created by Example: %Y-%m-%d %H:%M');
		$after = gmdate('Y-m-d H:i');
		$this->assertContains($description, array('Created by Example: '.$before, 'Created by Example: '.$after));
	}
}
