<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/modules/mod_pinp.phtml';

class HeleneSyntaxTest extends TestCase
{
	/** @dataProvider acceptedSyntax */
	public function testBrowserFixturesAreAcceptedByPinp($code, $minimumPhp)
	{
		if (PHP_VERSION_ID < $minimumPhp) {
			$this->markTestSkipped('Requires PHP '.intdiv($minimumPhp, 10000).'.'.intdiv($minimumPhp % 10000, 100));
		}
		$source = "<pinp>\r\n".preg_replace('/\r?\n/', "\r\n", $code)."\r\n</pinp>";
		$compiler = new pinp('', 'object->', '$object->_');
		$compiled = $compiler->compile($source);
		$this->assertNull($compiler->error, (string)$compiler->error);
		$this->assertNotFalse($compiled);
		token_get_all($compiled, TOKEN_PARSE);
	}

	public static function acceptedSyntax()
	{
		$examples = json_decode(file_get_contents(dirname(__DIR__, 2).'/javascript/pinp-syntax-cases.json'), true, 512, JSON_THROW_ON_ERROR);
		foreach ($examples as $example) {
			yield $example['name'] => array($example['code'], $example['php']);
		}
	}
}
