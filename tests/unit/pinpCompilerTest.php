<?php

require_once(AriadneBasePath."/modules/mod_pinp.phtml");

class pinpCompilerTest extends AriadneBaseTest
{
	function testBaseCompile() {
		$template = <<<'EOD'
<pinp> $test = 'test'; </pinp>
EOD;
		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertEquals("<?php  \$object->test = 'test';  ?>",$res);
	}

	function testObjectVariables() {
		$template = <<<'EOD'
<pinp>
	$test = ar('store')->rememberShortcuts;
	ar('store')->rememberShortcuts = false;
</pinp>
EOD;
		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		if(isset($compiler->error)){
			var_dump($compiler);
			var_dump($res);
		}
	}


	function testclassOperator() {
		$template = <<<'EOD'
<pinp>
	MyClass::CONST_VALUE;
	parent::myFunc();
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
	}


	function testclone() {
		$template = <<<'EOD'
<pinp>
  $a = clone($b);
  $b = clone $b;
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
	}


	function testcomments() {
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
	}


	function testdefine() {
		$template = <<<'EOD'
<pinp>
	define("FOO", "BAR");
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
	}


	function testderef() {
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
	}


	function testfunctionCalls() {
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
	}


	function testheredoc() {
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
	}


	function testmagicMethods() {
		$template = <<<'EOD'
<pinp>
	$object->_call("phpFunc", array());
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
	}


	function teststring() {
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
	}


	function testtypeCasting() {
		$template = <<<'EOD'
<pinp>
	$bool_a		= (bool) 1;
	$bool_b		= (boolean) 1;
	$int_a		= (int) "2";
	$int_b		= (integer) "3";
	$float_a	= (float) "1.1";
	$float_b	= (double) "1.2";
	$float_c	= (real) "1.3";
	$string_a	= (string) 1;
	$array_a 	= (array) null;
	$object_a	= (object) Array("a" => "1", "b" => "2");
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
	}

	function testObjectArrayAccess() {
		$template = <<<'EOD'
<pinp>
	$res = range(0,10)[5];
	return $res;
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new object(); ?'.'>'.$res);
		$this->assertEquals(5,$ret);
	}


}
?>
