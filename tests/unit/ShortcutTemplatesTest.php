<?php

class ShortcutTemplatesTest extends AriadneBaseTest
{
	public function testTemplateReadsAndEditsStayOnTheShortcut()
	{
		$this->initAriadne();
		$targetPath = current(ar::get(TESTBASE)->call('system.new.phtml', array(
			'arNewType' => 'pobject',
			'arNewFilename' => '{5:id}',
			'en' => array('name' => 'Template target'),
			'nl' => array('name' => 'Template target')
		)));
		$this->assertIsString($targetPath);
		$shortcutPath = current(ar::get(TESTBASE)->call('system.new.phtml', array(
			'arNewType' => 'pshortcut',
			'arNewFilename' => '{5:id}',
			'path' => $targetPath,
			'en' => array('name' => 'Template shortcut'),
			'nl' => array('name' => 'Template shortcut')
		)));
		$this->assertIsString($shortcutPath);
		$target = current(ar::get($targetPath)->call('system.get.phtml'));
		$shortcut = current(ar::get($shortcutPath)->call('system.get.phtml'));
		$args = array('type' => 'pobject', 'function' => 'shortcut-test.html', 'language' => 'any', 'private' => false);
		$targetSource = "<pinp>\r\n\techo 'target';\r\n</pinp>";
		$shortcutSource = "<pinp>\r\n\techo 'shortcut source';\r\n</pinp>";

		try {
			$target->call('system.save.layout.phtml', $args + array('template' => $targetSource, 'default' => 1));
			$this->assertEmpty($target->error);
			$shortcut->call('system.save.layout.phtml', $args + array('template' => $shortcutSource, 'default' => 1));
			$this->assertEmpty($shortcut->error);
			$this->assertSame($shortcutSource, $shortcut->call('system.get.template.php', $args));
			$info = $shortcut->call('system.get.template.info.php', $args);
			$this->assertSame(strlen($shortcutSource), $info['size']);
			$templates = $shortcut->call('system.get.templates.info.php');
			$this->assertCount(1, $templates['templates']);
			$this->assertSame(strlen($shortcutSource), $templates['templates'][0]['size']);

			// Also cover a template that does not exist on the shortcut target.
			$args['function'] = 'shortcut-only.html';
			$shortcut->call('system.save.layout.phtml', $args + array('template' => $shortcutSource, 'default' => 1));
			$this->assertEmpty($shortcut->error);
			$this->assertSame($shortcutSource, $shortcut->call('system.get.template.php', $args));
			$this->assertSame(strlen($shortcutSource), $shortcut->call('system.get.template.info.php', $args)['size']);

			$args['function'] = 'shortcut-test.html';
			$editedSource = "<pinp>\r\n\techo 'edited shortcut';\r\n</pinp>";
			$shortcut->call('system.save.layout.phtml', $args + array('template' => $editedSource, 'default' => 1));
			$this->assertEmpty($shortcut->error);
			$this->assertSame($editedSource, $shortcut->call('system.get.template.php', $args));
			$this->assertSame($targetSource, $target->call('system.get.template.php', $args));
			$shortcut->call('system.delete.template.php', $args);
			$this->assertEmpty($shortcut->error);
			$this->assertSame($targetSource, $target->call('system.get.template.php', $args));
		} finally {
			$shortcut->call('system.delete.phtml');
			$target->call('system.delete.phtml');
		}
	}
}
