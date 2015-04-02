<?php
class ar_html_zenTest extends AriadneBaseTest {
	public function testSimple() {
		$input    = 'div>span>a';
		$prepared = "\n\t<div>\n\t\t<span>\n\t\t\t<a></a>\n\t\t</span>\n\t</div>";
		$dom = ar('html')->zen($input);
		$this->assertInstanceOf('ar_html_zen',$dom);
		$this->assertEquals($prepared, (string)$dom);
	}

}
