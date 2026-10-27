<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/ar.php';
require_once dirname(__DIR__, 3).'/lib/modules/mod_pinp.phtml';

class PinpSmallSyntaxTest extends TestCase
{
	private function compile($body)
	{
		$source = "<pinp>\r\n".preg_replace('/\r?\n/', "\r\n", $body)."\r\n</pinp>";
		$compiler = new pinp('', 'object->', '$object->_');
		$compiled = $compiler->compile($source);
		$this->assertNull($compiler->error, (string)$compiler->error);
		$this->assertNotFalse($compiled);
		return $compiled;
	}

	private function runTemplate($body, $variables = array())
	{
		$compiled = $this->compile($body);
		$object = new ar_core_pinpSandbox(new stdClass());
		foreach ($variables as $name => $value) $object->$name = $value;
		eval('?>'.$compiled);
		return $object;
	}

	/** @dataProvider coalescingValues */
	public function testCoalescingAssignmentIsLazy($variables, $expected, $expectedCalls)
	{
		$calls = 0;
		$variables['fallback'] = function () use (&$calls) { $calls++; return 'fallback'; };
		$object = $this->runTemplate('$value ??= $fallback(); $result = $value;', $variables);
		$this->assertSame($expected, $object->result);
		$this->assertSame($expectedCalls, $calls);
	}

	public static function coalescingValues()
	{
		return array(
			'missing' => array(array(), 'fallback', 1),
			'null' => array(array('value' => null), 'fallback', 1),
			'false' => array(array('value' => false), false, 0),
			'zero' => array(array('value' => 0), 0, 0),
			'empty string' => array(array('value' => ''), '', 0),
			'empty array' => array(array('value' => array()), array(), 0)
		);
	}

	/** @dataProvider indexedAssignments */
	public function testCompoundAssignmentEvaluatesIndexOnce($code, $items, $expected)
	{
		$calls = 0;
		$object = $this->runTemplate($code, array(
			'items' => $items,
			'index' => function () use (&$calls) { $calls++; return 'key'; }
		));
		$this->assertSame($expected, $object->items['key']);
		$this->assertSame(1, $calls);
	}

	public static function indexedAssignments()
	{
		return array(
			'coalesce absent' => array('$items[$index()] ??= 7;', array(), 7),
			'coalesce present' => array('$items[$index()] ??= 7;', array('key' => 3), 3),
			'power' => array('$items[$index()] **= 3;', array('key' => 2), 8)
		);
	}

	public function testAssignmentsWorkForPropertiesNestedArraysAndExpressions()
	{
		$record = new stdClass();
		$object = $this->runTemplate(<<<'CODE'
$record->number ??= 2;
$result = ($record->number **= 3);
$items['nested']['value'] ??= 5;
$items['nested']['value'] **= 2;
$chained = ($first ??= $second ??= 9);
CODE
		, array('record' => $record));
		$this->assertSame(8, $record->number);
		$this->assertSame(8, $object->result);
		$this->assertSame(25, $object->items['nested']['value']);
		$this->assertSame(array(9, 9, 9), array($object->first, $object->second, $object->chained));
	}

	public function testPowerAssignmentAndExistingOperatorsKeepTheirPrecedence()
	{
		$object = $this->runTemplate(<<<'CODE'
$value = 2;
$value **= 3 ** 2;
$power = 2 ** 3;
$value *= 2;
$value += 1;
$fallback = $missing ?? 4;
$ternary = $power ? 5 : 6;
$text = '??= **=';
CODE
		);
		$this->assertSame(1025, $object->value);
		$this->assertSame(8, $object->power);
		$this->assertSame(4, $object->fallback);
		$this->assertSame(5, $object->ternary);
		$this->assertSame('??= **=', $object->text);
	}

	/** @dataProvider throwableKinds */
	public function testMultiCatchBindsEitherThrowableInSandbox($exception)
	{
		$object = $this->runTemplate(<<<'CODE'
try {
	throw $exception;
} catch (\RuntimeException | /* either type */ \Error $caught) {
	$result = $caught;
} finally {
	$finished = true;
}
CODE
		, array('exception' => $exception));
		$this->assertSame($exception, $object->result);
		$this->assertTrue($object->finished);
	}

	public static function throwableKinds()
	{
		return array(
			'exception' => array(new RuntimeException('Example')),
			'error' => array(new Error('Example')),
			'error subclass' => array(new TypeError('Example'))
		);
	}

	public function testCatchWithoutVariableDoesNotOverwriteScope()
	{
		$object = $this->runTemplate(<<<'CODE'
try { throw $exception; }
catch (RuntimeException) { $first = true; }
try { throw $error; }
catch (RuntimeException | Error) { $second = true; }
CODE
		, array('exception' => new RuntimeException(), 'error' => new Error(), 'caught' => 'keep'));
		$this->assertTrue($object->first);
		$this->assertTrue($object->second);
		$this->assertSame('keep', $object->caught);
	}

	public function testUnmatchedUnionFallsThroughToNextCatch()
	{
		$exception = new LogicException();
		$object = $this->runTemplate(<<<'CODE'
try { throw $exception; }
catch (RuntimeException | Error) { $wrong = true; }
catch (Exception $caught) { $result = $caught; }
CODE
		, array('exception' => $exception));
		$this->assertFalse(isset($object->wrong));
		$this->assertSame($exception, $object->result);
	}

	public function testTrailingParameterCommasWorkInCallbacksAndNestedClosures()
	{
		$object = $this->runTemplate(<<<'CODE'
$callback = function (
	$x,
	$y,
) { return $x + $y; };
$result = $callback(2, 3);
$mapped = array_map(function ($item,) { return $item * 2; }, [1, 2]);
$outer = function ($x,) {
	$inner = static function ($y,) { return $y + 1; };
	return $inner($x);
};
$nested = $outer(5);
CODE
		);
		$this->assertSame(5, $object->result);
		$this->assertSame(array(2, 4), $object->mapped);
		$this->assertSame(6, $object->nested);
	}

	/** @dataProvider malformedSyntax */
	public function testMalformedSyntaxStillFails($code)
	{
		$compiler = new pinp('', 'object->', '$object->_');
		$compiler->compile("<pinp>\r\n".$code."\r\n</pinp>");
		$this->assertNotEmpty($compiler->error);
	}

	public static function malformedSyntax()
	{
		return array_map(function ($code) { return array($code); }, array(
			'$value ??= ;', '$value **= ;', '1 ??= 2;',
			'try {} catch () {}', 'try {} catch (Exception |) {}',
			'try {} catch (Exception || Error $e) {}',
			'$f = function (,) {};', '$f = function ($x,,) {};'
		));
	}
}
