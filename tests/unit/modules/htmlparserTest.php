<?php

require_once(AriadneBasePath."/modules/mod_htmlparser.php");

class htmlparserTest extends AriadneBaseTest
{
	public function testSimple() {
		$html = '<html><head><title>test</title></head><body>testbody</body></html>';
		$compiled = htmlparser::parse($html);
		$clean = htmlparser::compile($compiled);
		$this->assertEquals($html,$clean);
	}
}
?>
