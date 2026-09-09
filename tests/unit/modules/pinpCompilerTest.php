<?php

require_once(AriadneBasePath."/modules/mod_pinp.phtml");

class pinpCompilerTest extends AriadneBaseTest
{
	public function testBaseCompile() {
		$template = <<<'EOD'
<pinp> $test = 'test'; </pinp>
EOD;
		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertEquals("<?php  \$object->test = 'test';  ?>",$res);
		$this->assertTrue((bool)$res);
	}

	public function testObjectVariables() {
		$template = <<<'EOD'
<pinp>
	$test = ar('store')->rememberShortcuts;
	ar('store')->rememberShortcuts = false;
</pinp>
EOD;
		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testclassOperator() {
		$template = <<<'EOD'
<pinp>
	MyClass::CONST_VALUE;
	parent::myFunc();
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testclone() {
		$template = <<<'EOD'
<pinp>
  $a = clone($b);
  $b = clone $b;
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testcomments() {
		$template = <<<'EOD'
<pinp>
// This is a comment
/*
  This is a multiline comment
*/
# This is also a comment
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testdefine() {
		$template = <<<'EOD'
<pinp>
	define("FOO", "BAR");
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testderef() {
		$template = <<<'EOD'
<pinp>
	$func = "readfile";
	${$func}();

	$$a;
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testfunctionCalls() {
		$template = <<<'EOD'
<pinp>
	$a->_frop();
	_frop();
	$a->{"_frop"}();
	$$a();
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testheredoc() {
		$template = <<<'EOD'
<?php



<pinp>
$MyVar = "frop";
$frop = "Mijn string";
$str = <<<EOT
   Example of string {$frop}
spanning multiple lines
using heredoc syntax.

MyVar: $MyVar;
De inhoud van de $MyVar variabel is: ${$MyVar}

EOT;

echo "$str\n";
</pinp>

EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testmagicMethods() {
		$template = <<<'EOD'
<pinp>
	$object->_call("phpFunc", array());
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function teststring() {
		$template = <<<'EOD'
<pinp>
$a = "MyVar";
$MyVar = "MyVar Value";
echo "run(\${a}): ${"a"} abc\n";
echo "run({\$a}): {$a} abc\n";
echo "run(\${\$a}): ${$a.substr("", 0, 0)} abc\n";
echo "run(\$a): $a[0]->frop()
 abc xyz\n";
echo "frop: $$a;\n";

</pinp>

EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}

	public function testDoubleQuotedStringDollarAnchor() {
		$template = <<<'EOD'
<pinp>
	preg_match("/member;range=(\d+)-(.+)$/", $key, $matches);
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
		$this->assertStringContainsString('preg_match("/member;range=(\d+)-(.+)$/", $object->key, $object->matches)', $res);
	}

	public function testWritableArgumentsKeepVariablePrefix() {
		$templates = array(
			"unset" => '<pinp>unset($value);</pinp>',
			"isset" => '<pinp>isset($value);</pinp>',
			"array_pop" => '<pinp>$values = array(1); array_pop($values);</pinp>',
			"array_shift" => '<pinp>$values = array(1); array_shift($values);</pinp>',
			"array_unshift" => '<pinp>$values = array(); array_unshift($values, 1);</pinp>',
			"shuffle" => '<pinp>$values = array(1); shuffle($values);</pinp>',
			"array_splice" => '<pinp>$values = array(1); array_splice($values, 0, 1);</pinp>',
			"array_multisort" => '<pinp>$values = array(1); array_multisort($values);</pinp>',
			"array_push" => '<pinp>$values = array(); array_push($values, 1);</pinp>',
			"preg_match_all" => '<pinp>preg_match_all("/member;range=(\d+)-(.+)$/", $key, $matches);</pinp>',
			"parse_str" => '<pinp>parse_str("a=1", $output);</pinp>',
			"sscanf" => '<pinp>sscanf("1", "%d", $output);</pinp>',
			"sort" => '<pinp>$values = array(2, 1); sort($values);</pinp>',
			"rsort" => '<pinp>$values = array(2, 1); rsort($values);</pinp>',
			"ksort" => '<pinp>$values = array("b" => 2); ksort($values);</pinp>',
			"foreach" => '<pinp>$values = array(1); foreach ($values as $value) { echo $value; }</pinp>',
			"increments" => '<pinp>$value = 1; $value++; ++$value;</pinp>'
		);

		foreach ($templates as $name => $template) {
			$compiler = new pinp("header", "object->", "\$object->_");
			$res = $compiler->compile($template);
			$this->assertNull($compiler->error, $name);
			$this->assertTrue((bool)$res, $name);
			$this->assertStringNotContainsString(' object->', $res, $name);
			$this->assertStringNotContainsString('(object->', $res, $name);
		}
	}

	public function testLegacyEachWhileCompilesToForeach() {
		$templates = array(
			"key-value" => array(
				"source" => '<pinp>while (list($key, $value)=each($values)) { echo $key; }</pinp>',
				"expected" => 'foreach ((($object->values ?? null) ?? array()) as $object->key => $object->value)'
			),
			"key-only" => array(
				"source" => '<pinp>while (list($key)=each($values)) { echo $key; }</pinp>',
				"expected" => 'foreach ((($object->values ?? null) ?? array()) as $object->key => $__pinp_each_value)'
			),
			"value-only" => array(
				"source" => '<pinp>while (list(, $value)=each($values)) { echo $value; }</pinp>',
				"expected" => 'foreach ((($object->values ?? null) ?? array()) as $__pinp_each_key => $object->value)'
			),
			"extra-parens" => array(
				"source" => '<pinp>while ((list($key, $value)=each($values))) { echo $value; }</pinp>',
				"expected" => 'foreach ((($object->values ?? null) ?? array()) as $object->key => $object->value)'
			)
		);

		foreach ($templates as $name => $template) {
			$compiler = new pinp("header", "object->", "\$object->_");
			$res = $compiler->compile($template["source"]);
			$this->assertNull($compiler->error, $name);
			$this->assertTrue((bool)$res, $name);
			$this->assertStringContainsString($template["expected"], $res, $name);
			$this->assertStringNotContainsString('while (list', $res, $name);
			$this->assertStringNotContainsString('each(', $res, $name);
		}
	}


	public function testtypeCasting() {
		$template = <<<'EOD'
<pinp>
	$bool_a		= (bool) 1;
	$bool_b		= (boolean) 1;
	$int_a		= (int) "2";
	$int_b		= (integer) "3";
	$float_a	= (float) "1.1";
	$float_b	= (double) "1.2";
	$string_a	= (string) 1;
	$array_a 	= (array) null;
	$object_a	= (object) Array("a" => "1", "b" => "2");
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
		$this->assertStringContainsString('(bool) 1', $res);
		$this->assertStringContainsString('(int) "3"', $res);
		$this->assertStringContainsString('(float) "1.2"', $res);
		$this->assertStringNotContainsString('(boolean)', $res);
		$this->assertStringNotContainsString('(integer)', $res);
		$this->assertStringNotContainsString('(double)', $res);
	}

	public function testObjectArrayAccess() {
		$template = <<<'EOD'
<pinp>
	$res = range(0,10)[5];
	return $res;
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertEquals(5,$ret);
	}

	public function testFluentInterface() {
		$template = <<<'EOD'
<pinp>
	$res = ar('error')->raiseError('test',42)->getMessage();
	return $res;
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertEquals('test',$ret);
	}

	public function testCurlyBrace() {
		$template = <<<'EOD'
<pinp>
	$var = array (0,1,42,3);
	$test = 2;
	return $var[$test];
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertEquals(42,$ret);
	}

	public function testClosuresCallback() {
		$template = <<<'EOD'
<pinp>
	$test = array(1,2,3,4,5,6,7,8,9,10);
	$var = function ($a) {
		return $a*2;
	};

	return array_map($var, $test);
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertEquals(20,end($ret));
	}

	public function testClosures() {
		$template = <<<'EOD'
<pinp>
	$test = 'outside';
	$var = function () {
		return $test??null;
	};

	return $var($test);
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertNull($ret);
	}

	public function testSandboxArrayAccess() {
		$template = <<<'EOD'
<pinp>
	$result['foo'] = 'bar';
	return $result['foo'];
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertEquals('bar',$ret);
	}

	public function testClosuresThisAvailable() {
		$template = <<<'EOD'
<pinp>
	$var = function ($outsidethis) {
		return ($outsidethis == $this);
	};

	return $var($this);
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertTrue($ret);
	}

	public function testClosuresIlligalCallString() {
		$template = <<<'EOD'
<pinp>
	$call1 = 'rand';
	return $call1();
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertInstanceOf('ar_error',$ret);
	}

	public function testClosuresIlligalCallArray() {
		$template = <<<'EOD'
<pinp>
	$call2 = array ('pobject','make_path');
	return $call2();
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertInstanceOf('ar_error',$ret);
	}

	public function testClosuresNesting() {
		$template = <<<'EOD'
<pinp>
	$func = function ($a) {
		$b = function ($a) {
			return 2*$a;
		};
		return $b($a)+1;
	};

	return $func(3);
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertEquals(7,$ret);
	}

	public function testReferences() {
		$template = <<<'EOD'
<pinp>
	$frop = 'frop';
	$foo = &$frop;
	$bar =& $frop;
	$frop = 'frml';	
	return array( $foo, $bar );
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertEquals(['frml','frml'], $ret);
	}

	public function testArrayPush() {
		$template = <<<'EOD'
<pinp>
	$foo = array();
	$bar = 'bar';
	array_push($foo, array('bar' => 'bar'.$bar));
	return $foo;
</pinp>
EOD;
		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		echo $res;
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertEquals([['bar' => 'barbar']], $ret);
	}		

	public function testArrayPushNormal() {
		$template = <<<'EOD'
<pinp>
	$foo = array();
	$bar = 'bar';
	array_push(
		$foo, 
		$bar
	);
	return $foo;
</pinp>
EOD;
		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		echo $res;
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new ar_core_pinpSandbox($this); ?'.'>'.$res);
		$this->assertEquals(['bar'], $ret);
	}		

}
