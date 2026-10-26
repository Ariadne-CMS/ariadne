<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/ar.php';
require_once dirname(__DIR__, 3).'/lib/modules/mod_pinp.phtml';

class PinpReferenceArgumentsTest extends TestCase
{
	private function runTemplate($source, $variables = array())
	{
		$object = new ar_core_pinpSandbox(new stdClass());
		foreach ($variables as $name => $value) {
			$object->$name = $value;
		}
		$compiler = new pinp('', 'object->', '$object->');
		$compiled = $compiler->compile('<pinp>'.$source.'</pinp>');
		$this->assertNull($compiler->error);
		eval('?>'.$compiled);
		return $object;
	}

	/** @dataProvider sortFunctions */
	public function testSortsModifyTheOriginalArray($function, $expected)
	{
		$object = $this->runTemplate('$result = '.$function.'($items);', array('items' => array('b' => 20, 'a' => 10)));
		$this->assertTrue($object->result);
		$this->assertSame($expected, $object->items);
	}

	public static function sortFunctions()
	{
		return array(
			array('sort', array(10, 20)), array('rsort', array(20, 10)),
			array('asort', array('a' => 10, 'b' => 20)), array('arsort', array('b' => 20, 'a' => 10)),
			array('ksort', array('a' => 10, 'b' => 20)), array('krsort', array('b' => 20, 'a' => 10)),
			array('natsort', array('a' => 10, 'b' => 20)), array('natcasesort', array('a' => 10, 'b' => 20))
		);
	}

	/** @dataProvider callbackSortFunctions */
	public function testCallbackSortsModifyTheOriginalArray($function, $expected)
	{
		$object = $this->runTemplate($function.'($items, $compare);', array(
			'items' => array('b' => 20, 'a' => 10),
			'compare' => function ($a, $b) { return $a <=> $b; }
		));
		$this->assertSame($expected, $object->items);
	}

	public static function callbackSortFunctions()
	{
		return array(
			array('usort', array(10, 20)),
			array('uasort', array('a' => 10, 'b' => 20)),
			array('uksort', array('a' => 10, 'b' => 20))
		);
	}

	public function testWalksAndArrayPointersWriteBack()
	{
		$object = $this->runTemplate('array_walk($items, $increment); array_walk_recursive($nested, $increment);'
			.'$first = reset($items); $second = next($items); $previous = prev($items); $last = end($items);', array(
			'items' => array(10, 20), 'nested' => array(array(30)),
			'increment' => function (&$value) { $value++; }
		));
		$this->assertSame(array(11, 21), $object->items);
		$this->assertSame(array(array(31)), $object->nested);
		$this->assertSame(array(11, 21, 11, 21), array($object->first, $object->second, $object->previous, $object->last));
		$this->assertSame(1, key($object->items));
	}

	/** @dataProvider outputTargets */
	public function testOutputParametersRemainWritable($initial, $target)
	{
		$object = $this->runTemplate($initial.'$result = str_replace("a", "b", "aaa", '.$target.'); $count = '.$target.';');
		$this->assertSame('bbb', $object->result);
		$this->assertSame(3, $object->count);
	}

	public static function outputTargets()
	{
		return array(
			array('', '$output'), array('$output = 0;', '$output'),
			array('$output = array();', '$output["count"]'),
			array('$output = array(); $key = "count";', '$output[$key]'),
			array('$output = json_decode("{}");', '$output->count')
		);
	}

	public function testOtherOutputParameters()
	{
		$object = $this->runTemplate('parse_str("a=1", $query); preg_match("/(a)/", "a", $matches);'
			.'$text = "42"; settype($text, "integer"); sscanf("12 34", "%d %d", $first, $second);'
			.'similar_text("same", "same", $percent);');
		$this->assertSame(array('a' => '1'), $object->query);
		$this->assertSame(array('a', 'a'), $object->matches);
		$this->assertSame(42, $object->text);
		$this->assertSame(12, $object->first);
		$this->assertSame(34, $object->second);
		$this->assertEquals(100, $object->percent);
	}

	public function testXmlOutputParameters()
	{
		if (!extension_loaded('xml')) $this->markTestSkipped('XML extension required.');
		$object = $this->runTemplate('$result = xml_parse_into_struct($parser, "<root>value</root>", $values, $index);',
			array('parser' => xml_parser_create()));
		$this->assertSame(1, $object->result);
		$this->assertSame('value', $object->values[0]['value']);
		$this->assertSame(array('ROOT' => array(0)), $object->index);
	}

	public function testInputAndIndexGuardsArePreserved()
	{
		$compiler = new pinp('', 'object->', '$object->');
		$compiled = $compiler->compile('<pinp>array_push($items[$key], $value); str_replace($from, $to, $text, $count);</pinp>');
		$this->assertNull($compiler->error);
		foreach (array('key', 'value', 'from', 'to', 'text') as $name) {
			$this->assertStringContainsString('($object->'.$name.' ?? null)', $compiled);
		}
		$object = $this->runTemplate('array_push($items, $missing);', array('items' => array()));
		$this->assertSame(array(null), $object->items);
	}

	public function testReferenceHandlingDoesNotRewriteCompoundExpressions()
	{
		$compiler = new pinp('', 'object->', '$object->');
		$compiled = $compiler->compile('<pinp>sort($a + $b);</pinp>');
		$this->assertMatchesRegularExpression('/\(\$object->a\s+\?\? null\)\s*\+\s*\(\$object->b\s+\?\? null\)/', $compiled);
	}

	public function testWhitelistedNativeReferenceParametersAreSupported()
	{
		$compiler = new pinp('', 'object->', '$object->');
		foreach (array_keys($compiler->allowed_functions) as $name) {
			if (!function_exists($name)) continue;
			$parameters = (new ReflectionFunction($name))->getParameters();
			$args = array();
			foreach ($parameters as $position => $parameter) $args[] = '$arg'.$position;
			$compiled = $compiler->compile('<pinp>'.$name.'('.implode(', ', $args).');</pinp>');
			foreach ($parameters as $position => $parameter) {
				if ($parameter->isPassedByReference()) {
					$this->assertStringNotContainsString('($object->arg'.$position.' ?? null)', $compiled, $name.' argument '.$position);
				}
			}
		}
	}

	public function testLoaderErrorParameterRemainsWritable()
	{
		$compiler = new pinp('', 'object->', '$object->');
		$compiled = $compiler->compile('<pinp>ldRegisterFile($field, $error);</pinp>');
		$this->assertNull($compiler->error);
		$this->assertStringContainsString('($object->field ?? null)', $compiled);

		$register = function ($field, &$error) {
			$error = 'Upload failed';
		};
		$object = new stdClass();
		eval('?>'.str_ireplace('ldRegisterFile(', '$register(', $compiled));
		$this->assertSame('Upload failed', $object->error);
	}

	public function testBinaryCastUsesCanonicalSpelling()
	{
		$object = $this->runTemplate('$result = (binary) 42;');
		$this->assertSame('42', $object->result);
	}
}
