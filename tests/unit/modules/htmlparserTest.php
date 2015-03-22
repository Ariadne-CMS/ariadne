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

	public function testDataAtributesDot() {
		$html = '<body>testbody<span data.dot="dot">frml</span></body>';
		$compiled = htmlparser::parse($html);
		$clean = htmlparser::compile($compiled);
		$this->assertEquals($html, $clean);
	}
	public function testDataAtributesUnderscore() {
		$html = '<body>testbody<span data_underscore="underscore">frml</span></body>';
		$compiled = htmlparser::parse($html);
		$clean = htmlparser::compile($compiled);
		$this->assertEquals($html, $clean);
	}
	public function testDataAtributesDash() {
		$html = '<body>testbody<span data-dash="dash">frml</span></body>';
		$compiled = htmlparser::parse($html);
		$clean = htmlparser::compile($compiled);
		$this->assertEquals($html, $clean);
	}

}
?>
