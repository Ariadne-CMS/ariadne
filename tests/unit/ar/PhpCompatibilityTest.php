<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/ar.php';

class PhpCompatibilityTest extends TestCase
{
	public function testCsvSaveAcceptsDefaultAndNullConfiguration()
	{
		$file = new ar_content_filesFile(fopen('php://memory', 'w+'));
		$data = array(array('name', 'age'), array('Alice', '42'));
		$this->assertSame(17, ar_formats_csv::save($file, $data));
		$file->rewind();
		$this->assertSame("name,age\nAlice,42", $file->getContents());
		$file->truncate(0);
		$file->rewind();
		$this->assertSame(17, ar_formats_csv::save($file, $data, null));
		$file->rewind();
		$this->assertSame("name,age\nAlice,42", $file->getContents());
	}

	public function testCsvArrayAccessAndStreamIteration()
	{
		$row = new ar_formats_csvLine(array('Alice', '42'), array('headers' => array('name', 'age')));
		$this->assertSame('Alice', $row[0]);
		$row[1] = '43';
		$this->assertSame('43', $row['age']);
		$this->assertTrue(isset($row['name']));
		unset($row['name']);
		$this->assertFalse(isset($row['name']));
		$file = new ar_content_filesFile(fopen('php://memory', 'w+'));
		$file->write("Alice,42\nBob,43");
		$file->rewind();
		$stream = ar_formats_csv::stream($file, array('headers' => array('name', 'age')));
		$rows = array();
		foreach ($stream as $index => $line) $rows[$index] = (array) $line;
		$this->assertSame(array(array('name' => 'Alice', 'age' => '42'), array('name' => 'Bob', 'age' => '43')), $rows);
	}

	public function testRipcordTransportsWithoutNetworkAccess()
	{
		require_once dirname(__DIR__, 3).'/lib/includes/ripcord/ripcord.php';
		ripcord::load('Ripcord_Client');
		ripcord::load('Ripcord_Documentor');
		$stream = new Ripcord_Transport_Stream();
		$this->assertSame(file_get_contents(__FILE__), $stream->post('file://'.__FILE__, ''));
		$this->assertIsArray($stream->responseHeaders);
		if (extension_loaded('curl')) {
			$curl = new Ripcord_Transport_CURL(array(CURLOPT_HEADER => false));
			$this->assertSame(file_get_contents(__FILE__), $curl->post('file://'.__FILE__, ''));
			$this->expectException(Ripcord_TransportException::class);
			$curl->post('file://'.__DIR__.'/does-not-exist.xml', '');
		}
	}

	public function testCoreClassesLoadAndAcceptTheirExistingConstructorArguments()
	{
		$ariadne = new ar_core_ariadne(new stdClass(), new stdClass(), null);
		$this->assertInstanceOf(ar_core_ariadne_interface::class, $ariadne);
		$cached = new ar_core_ariadneCache(new stdClass(), new stdClass(), null, new stdClass());
		$this->assertInstanceOf(ar_core_ariadne_interface::class, $cached);
		$nls = new ar_core_nls((object) array('default' => 'nl', 'list' => array('nl' => 'Nederlands')));
		$this->assertSame('nl', $nls->default);
		$this->assertSame(array('nl' => 'Nederlands'), $nls->available);
		$this->assertTrue((new ReflectionClass(ar_core_session::class))->isInstantiable());
	}

	public function testDiffDoesNotCreateDynamicProperties()
	{
		$html = (string) ar_beta_diff::diff('one', 'two');
		$this->assertStringContainsString('diff-deletedline', $html);
		$this->assertStringContainsString('diff-addedline', $html);
	}
}
