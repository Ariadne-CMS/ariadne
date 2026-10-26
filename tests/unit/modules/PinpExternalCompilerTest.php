<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/ar.php';
require_once dirname(__DIR__, 3).'/lib/modules/mod_pinp.phtml';

class PinpExternalCompilerTest extends TestCase
{
	/** @dataProvider unsupportedCompilers */
	public function testUnsupportedCompilerReportsAnError($engine)
	{
		$compiler = new pinp('', 'object->', '$object->');
		$result = $compiler->compile('<compile:'.$engine.'>@color: red; .x { color: @color; }</compile:'.$engine.'>');
		$this->assertSame('', $result);
		$this->assertStringContainsString("Compiler '$engine' is not supported", $compiler->error);
		$this->assertStringContainsString('line 1', $compiler->error);
	}

	public static function unsupportedCompilers()
	{
		return array(array('lessc'), array('LESSC'), array('unknown'));
	}

	/** @dataProvider supportedCompilers */
	public function testOtherCompilersStillProduceOutput($engine, $source, $expected)
	{
		global $AR;
		$previousAR = $AR;
		$AR = (object) array('PINP_Functions' => '');
		try {
			$compiler = new pinp('', 'object->', '$object->');
			$result = $compiler->compile('<compile:'.$engine.'>'.$source.'</compile:'.$engine.'>');
			$this->assertNull($compiler->error);
			$this->assertSame($expected, $result);
		} finally {
			$AR = $previousAR;
		}
	}

	public static function supportedCompilers()
	{
		return array(
			array('jsmin', 'var x = 1;', 'var x=1;'),
			array('test', 'hello', '[[ TEST ]] hello [[ /TEST ]]')
		);
	}
}
