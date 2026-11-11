<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/ar.php';
require_once dirname(__DIR__, 3).'/lib/modules/mod_pinp.phtml';

class PinpStringInterpolationTest extends TestCase
{
	public function testReportedGroupNamesInProductionSandbox()
	{
		$source = implode("\r\n", array(
			'<pinp>',
			"\t".'$klas = "PN6V";',
			"\t".'$vak = "biol";',
			"\t".'$groups = array();',
			"\t".'$groups[] = "Leerling Klas $klas vak $vak";',
			"\t".'$groups[] = "Klas $klas vak $vak";',
			"\t".'return $groups;',
			'</pinp>'
		));
		$compiler = new pinp('', 'local->', '$AR_this->_');
		$compiled = $compiler->compile($source);
		$this->assertNull($compiler->error, (string)$compiler->error);
		$local = new ar_core_pinpSandbox(new stdClass());
		$groups = eval('?>'.$compiled);
		$this->assertSame(array('Leerling Klas PN6V vak biol', 'Klas PN6V vak biol'), $groups);
	}

	/** @dataProvider interpolatedStrings */
	public function testInterpolationPreservesLiteralWhitespace($literal, $expected)
	{
		$source = "<pinp>\r\n\t\$result = ".$literal.";\r\n</pinp>";
		$compiler = new pinp('', 'object->', '$object->_');
		$compiled = $compiler->compile($source);
		$this->assertNull($compiler->error, (string)$compiler->error);
		$object = (object) array(
			'klas' => 'PN6V',
			'vak' => 'biol',
			'values' => array('PN6V'),
			'group' => (object) array('klas' => 'PN6V')
		);
		eval('?>'.$compiled);
		$this->assertSame($expected, $object->result);
	}

	public static function interpolatedStrings()
	{
		return array(
			'vakgroep' => array('"Klas $klas vak $vak"', 'Klas PN6V vak biol'),
			'leerling vakgroep' => array('"Leerling Klas $klas vak $vak"', 'Leerling Klas PN6V vak biol'),
			'multiple spaces' => array('"$klas   vak"', 'PN6V   vak'),
			'trailing spaces' => array('"$klas  "', 'PN6V  '),
			'tab' => array('"$klas'."\t".'vak"', "PN6V\tvak"),
			'line break' => array('"$klas'."\r\n\t".'vak"', "PN6V\r\n\tvak"),
			'array value' => array('"$values[0] vak"', 'PN6V vak'),
			'object property' => array('"$group->klas vak"', 'PN6V vak'),
			'braced variable' => array('"{$klas} vak"', 'PN6V vak'),
			'braced array' => array('"{$values[0]} vak"', 'PN6V vak'),
			'braced property' => array('"{$group->klas} vak"', 'PN6V vak'),
			'missing variable' => array('"$missing vak"', ' vak'),
			'adjacent variables' => array('"$klas$vak"', 'PN6Vbiol'),
			'punctuation' => array('"$klas-vak"', 'PN6V-vak'),
			'escaped whitespace' => array('"$klas\t\n vak"', "PN6V\t\n vak"),
			'single quoted string' => array("'\$klas vak'", '$klas vak'),
			'heredoc' => array("<<<TEXT\r\n".'Klas $klas vak $vak'."\r\nTEXT", 'Klas PN6V vak biol'),
			'heredoc array and property' => array("<<<TEXT\r\n".'$values[0] vak $group->klas end'."\r\nTEXT", 'PN6V vak PN6V end')
		);
	}
}
