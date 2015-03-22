<?php

require_once(AriadneBasePath."/modules/mod_htmlcleaner.php");

class htmlcleanerTest extends AriadneBaseTest
{
	public function testSimple() {
		$html = '<html><head><title>test</title></head><body>testbody</body></html>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

	public function testFormSelectOptionSelected() {
		$html = '<body>testbody<form><select><option selected></option></select></form></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

	public function testDataAtributesDot() {
		$html = '<body>testbody<span data.dot="dot">frml</span></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

	public function testDataAtributesUnderscore() {
		$html = '<body>testbody<span data_underscore="underscore">frml</span></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

	public function testDataAtributesDash() {
		$html = '<body>testbody<span data-dash="dash">frml</span></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

}
?>
