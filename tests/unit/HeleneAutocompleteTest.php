<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2).'/bin/utils/helene-autocomplete.php';
set_include_path(dirname(__DIR__, 2).'/vendor/pear/pear-core-minimal/src'.PATH_SEPARATOR.get_include_path());
require_once dirname(__DIR__, 2).'/vendor/pear/archive_tar/Archive/Tar.php';

class HeleneAutocompleteTest extends TestCase
{
	public function testCatalogueMatchesExposedApis()
	{
		$source = file_get_contents(dirname(__DIR__, 2).'/www/js/helene-autocomplete.js');
		preg_match('/var ariadneList = (\{.*?\});/s', $source, $functions);
		preg_match('/var ARList = (\{.*?\});/s', $source, $modules);
		$catalogue = heleneAutocompleteCatalogue();
		$this->assertSame($catalogue['functions'], json_decode($functions[1], true, 512, JSON_THROW_ON_ERROR));
		$this->assertSame($catalogue['modules'], json_decode($modules[1], true, 512, JSON_THROW_ON_ERROR));
		foreach (array('array_first', 'array_find', 'openssl_sign', 'set_time_limit', 'implode', 'strftime', 'ldGetRequestedHost', 'ar::construct', 'ar::callStatic', 'ar::unwrap') as $name) {
			$this->assertArrayHasKey($name, $catalogue['functions']);
		}
		foreach (array('extract', 'exec', 'unserialize', 'ar::autoload', 'ar::getObject') as $name) {
			$this->assertArrayNotHasKey($name, $catalogue['functions']);
		}
		$this->assertContains('attribute', $catalogue['modules']['html'], 'Inherited public methods must be included');
		$this->assertContains('inputStream', $catalogue['modules']['loader']);
		$this->assertNotContains('parseChildren', $catalogue['modules']['html'], 'Protected methods must be excluded');
		$this->assertNotContains('allow', $catalogue['modules']['pinp'], 'Only exposed methods should be suggested');
		$this->assertNotContains('remove', $catalogue['modules']['events'], 'Respect explicit method allowlists');
	}

	public function testPackagedAutocompleteMatchesReadableSource()
	{
		$root = dirname(__DIR__, 2);
		$archive = new Archive_Tar($root.'/www/install/packages/lib.ax', 'gz');
		$source = $archive->extractInString('./templates/33/_pobject.helene.template.autocomplete.js.any.pinp');
		$this->assertSame(file_get_contents($root.'/www/js/helene-autocomplete.js'), $source);
		$this->assertDoesNotMatchRegularExpression('/(?<!\r)\n/', $source);
		$compiler = new pinp('', 'object->', '$object->_');
		$compiled = $compiler->compile($source);
		$this->assertNull($compiler->error, (string)$compiler->error);
		ob_start();
		try {
			eval('?>'.$compiled);
			$this->assertSame($source, ob_get_contents());
		} finally {
			ob_end_clean();
		}
		$manifestSource = $archive->extractInString('./templates/33/_pobject.helene.assets.json.any.pinp');
		$manifest = json_decode(substr($manifestSource, strpos($manifestSource, '</pinp>') + 7), true);
		$this->assertContains('helene.template.autocomplete.js', $manifest['templates']);
	}
}
