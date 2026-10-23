<?php

use PHPUnit\Framework\TestCase;

if (!defined('AriadneBasePath')) {
	define('AriadneBasePath', dirname(__DIR__, 2).'/lib');
}
require_once AriadneBasePath.'/modules/mod_pinp.phtml';
set_include_path(
	dirname(AriadneBasePath).'/vendor/pear/pear-core-minimal/src'.PATH_SEPARATOR.get_include_path()
);
require_once dirname(AriadneBasePath).'/vendor/pear/archive_tar/Archive/Tar.php';

class HelenePackageTest extends TestCase
{
	private $archive;

	protected function setUp(): void
	{
		$this->archive = new Archive_Tar(
			dirname(AriadneBasePath).'/www/install/packages/lib.ax',
			'gz'
		);
	}

	private function getTemplate($name, $language = 'any', $type = 'pobject')
	{
		$template = $this->archive->extractInString('./templates/33/_'.$type.'.'.$name.'.'.$language.'.pinp');
		$this->assertIsString($template, 'Missing Helene package template '.$name);
		return $template;
	}

	public function testFeatureCompleteTemplateOverviewIsPackaged()
	{
		$sidebar = $this->getTemplate('explore.sidebar.settings.extra.html');
		$this->assertStringContainsString("'dialog.templates.php'", $sidebar);
		$this->assertStringContainsString("make_ariadne_url().'helene.html'", $sidebar);
		$this->assertStringContainsString("['target'] = '_blank'", $sidebar);
		$this->assertStringContainsString("heleneWindows.focus('overview', id)", $sidebar);
		$this->assertStringContainsString("heleneWindows.reserve('overview', id)", $sidebar);
		$this->assertStringContainsString('window.heleneWindows.reserve && window.heleneWindows.release', $sidebar);
		$this->assertStringContainsString("muze.ariadne.explore.arshow('_blank', url)", $sidebar);
		$this->assertStringContainsString('htmlspecialchars(json_encode($path), ENT_QUOTES', $sidebar);
		$this->assertStringContainsString("localStorage.getItem(prefix + 'entry.' + identity)", $sidebar);
		$this->assertStringContainsString("localStorage.setItem(prefix + 'request'", $sidebar);
		$this->assertStringContainsString('muze.include(helper)', $sidebar);

		$header = $this->getTemplate('helene.header.html');
		$this->assertStringContainsString(
			'class="helene-toggle-option active" data-helene-toggle="viewtype" data-helene-option="helene-detailview"',
			$header
		);
		$this->assertStringContainsString(".'dialog.templates.php'", $header);
		$this->assertStringContainsString('target="_blank" rel="noopener"', $header);
		$this->assertStringContainsString("gettext('helene:open-old-overview')", $header);
		$this->assertStringContainsString('name="heleneSort"', $header);
		$this->assertStringContainsString('value="name"', $header);
		$this->assertStringContainsString('value="date"', $header);
		$this->assertStringContainsString('value="svn"', $header);
		$this->assertStringContainsString("gettext('helene:sort-svn')", $header);

		$footer = $this->getTemplate('helene.foot.html');
		$this->assertStringContainsString("document.querySelector('.helene-templates-block')", $footer);
		$this->assertStringContainsString("var viewtype = 'helene-detailview';", $footer);
		$this->assertStringContainsString("target.classList.contains('helene-toggle')", $footer);
		$this->assertStringContainsString("block.classList.remove('helene-closed')", $footer);
		$this->assertStringContainsString("block.classList.add('helene-open')", $footer);
		$this->assertStringContainsString("block.classList.remove('helene-open')", $footer);
		$this->assertStringContainsString("block.classList.add('helene-closed')", $footer);
		$this->assertStringNotContainsString("target.classList.contains('helene-toggle-option')", $footer);
		$this->assertStringContainsString("bus.send('helene.template.open', request)", $footer);
		$this->assertStringContainsString("window.heleneWindows.open('template', key, url", $footer);
		$this->assertStringNotContainsString("window.heleneWindows.register('overview'", $footer);
		$this->assertStringContainsString("hasParent(target, 'helene-template-link')", $footer);
		$this->assertStringContainsString('request.window.focus()', $footer);

		$head = $this->getTemplate('helene.head.html');
		$this->assertStringContainsString('helene.scripts.head.js', $head);
		$this->assertStringContainsString("window.heleneWindows.register('overview'", $head);

		$windowRegistry = $this->getTemplate('helene.scripts.head.js');
		$this->assertStringContainsString("var prefix = 'helene.windows.v1.'", $windowRegistry);
		$this->assertStringContainsString('global.localStorage.setItem', $windowRegistry);
		$this->assertStringContainsString("global.addEventListener('storage'", $windowRegistry);
		$this->assertStringContainsString("global.addEventListener('pagehide'", $windowRegistry);
		$this->assertStringContainsString("global.addEventListener('pageshow'", $windowRegistry);
		$this->assertStringContainsString('windowAnchors[i].heleneWindowRefs[record.identity] = global', $windowRegistry);
		$this->assertStringContainsString('focusDirect(windowIdentity, details)', $windowRegistry);
		$this->assertStringContainsString('if ( !target )', $windowRegistry);
		$this->assertStringContainsString('if ( target === global )', $windowRegistry);
		$this->assertStringContainsString('reserve: reserve', $windowRegistry);
		$this->assertStringContainsString('release: remove', $windowRegistry);
		$this->assertStringContainsString('remove(record);', $windowRegistry);

		$list = $this->getTemplate('helene.get.templates');
		$this->assertStringContainsString('system.get.templates.info.php', $list);
		$this->assertStringContainsString("'pobject::system.get.templates.info.php'", $list);
		$this->assertStringContainsString("\$templatesOb->implements('pshortcut')", $list);
		$this->assertStringContainsString("array_key_exists('templates', \$templateSet)", $list);
		$this->assertStringContainsString('system.get.template.info.php', $list);
		$this->assertStringContainsString('$templatesOb->data->config->pinp', $list);

		$byPath = $this->getTemplate('helene.get.templates.bypath');
		$this->assertStringContainsString("ar::getvar('heleneTemplatesRootPath') ?: \$path", $byPath);
		$this->assertStringContainsString('ar::parents($templatesRootPath)', $byPath);
		$this->assertStringContainsString("array(\$templatesRootPath => '')", $byPath);
		$this->assertStringContainsString("makePath(\$templatesRootPath.'../')", $byPath);
		$this->assertStringContainsString("if ( \$parentPath == '/' )", $byPath);
		$this->assertSame(2, substr_count($byPath, "ar::call('current:helene.get.templates'"));
		$this->assertStringContainsString("ar::call( 'current:helene.get.libraries'", $byPath);
		$this->assertStringNotContainsString("ar::call('helene.get.templates'", $byPath);

		$overview = $this->getTemplate('helene.html');
		$this->assertStringContainsString(
			"ar::putvar('heleneTemplatesRootPath', ar::getvar('arShortCut') ?: \$path)",
			$overview
		);

		$block = $this->getTemplate('helene.show.template-block.html');
		$this->assertStringContainsString('helene-template-metadata', $block);
		$this->assertStringContainsString('helene-svn-menu', $block);
		$this->assertStringContainsString('data-helene-template-id=', $block);
		$this->assertStringContainsString("ar::getvar('heleneSort')", $block);
		$this->assertStringContainsString("\$this->templateSort == 'date'", $block);
		$this->assertStringContainsString('($a[\'mtime\']??0) > ($b[\'mtime\']??0)', $block);
		$this->assertStringContainsString("in_array(\$aStatus, array('added', 'unversioned')) ? 0", $block);
		$this->assertStringContainsString("\$aStatus != 'normal' && \$aStatus != 'none'", $block);

		$layout = $this->getTemplate('helene.layout.css');
		$this->assertStringContainsString('body.helene-detailview .helene-svn-menu', $layout);
		$this->assertStringContainsString('margin-left: calc(-2em - 5px)', $layout);
		$this->assertStringContainsString('left: 280px;', $layout);
		$this->assertStringNotContainsString('left: 281px;', $layout);
		$this->assertMatchesRegularExpression('/\\.helene-template-svn \\{[^}]*position: relative;[^}]*z-index: 2;/s', $layout);

		$menu = $this->getTemplate('helene.template.menu.html');
		$this->assertStringContainsString(".'dialog.templates.edit.php'", $menu);
		$this->assertStringContainsString("'target' => '_blank'", $menu);
		$this->assertStringContainsString("gettext('helene:open-in-old-editor')", $menu);
		$this->assertStringContainsString('dialog.svn.templates.revert.php', $menu);
		$this->assertStringContainsString('dialog.svn.templates.update.php', $menu);

		$editorFooter = $this->getTemplate('helene.template.foot.html');
		$this->assertStringContainsString("bus.listen('helene.template.open'", $editorFooter);
		$this->assertStringContainsString("window.heleneWindows.register('template', editorTemplateID", $editorFooter);
		$this->assertStringContainsString('message.handled = true', $editorFooter);
		$this->assertStringContainsString('message.window = window', $editorFooter);
	}

	public function testChangedHeleneTemplatesCompile()
	{
		$templates = array(
			'explore.sidebar.settings.extra.html',
			'helene.foot.html',
			'helene.get.templates',
			'helene.get.templates.bypath',
			'helene.header.html',
			'helene.head.html',
			'helene.layout.css',
			'helene.nls',
			'helene.show.template-block.html',
			'helene.scripts.head.js',
			'helene.template.menu.html',
			'helene.template.foot.html',
			'helene.template.menu.js'
		);

		foreach ($templates as $name) {
			$compiler = new pinp('header', 'object->', '$object->_');
			$compiled = $compiler->compile(str_replace("\r", '', $this->getTemplate($name)));
			$this->assertNull($compiler->error, $name.': '.(string) $compiler->error);
			$this->assertNotFalse($compiled, $name.' did not compile');
		}
		$compiler = new pinp('header', 'object->', '$object->_');
		$compiler->compile(str_replace("\r", '', $this->getTemplate('helene.nls', 'nl')));
		$this->assertNull($compiler->error, 'helene.nls.nl: '.(string) $compiler->error);
	}

	public function testSidebarSettingsUseGenericExtensionHook()
	{
		$pobject = file_get_contents(AriadneBasePath.'/templates/pobject/explore.sidebar.settings.php');
		$this->assertStringNotContainsString('helene.html', $pobject);
		$this->assertStringContainsString("'explore.sidebar.settings.extra.html'", $pobject);

		$shortcut = file_get_contents(AriadneBasePath.'/templates/pshortcut/explore.sidebar.settings.php');
		$this->assertStringNotContainsString('helene.html', $shortcut);
		$this->assertStringContainsString("'explore.sidebar.settings.extra.html'", $shortcut);
		$this->assertStringNotContainsString("'explore.sidebar.tasks.extra.html'", $shortcut);

		$shortcutDefault = file_get_contents(AriadneBasePath.'/templates/pshortcut/default.phtml');
		$this->assertStringContainsString('$ARBeenHere[$arCallFunction][$path]-=1;', $shortcutDefault);
	}

	public function testClosingPinpTagIsTheLastContentByte()
	{
		$templates = array(
			'explore.sidebar.settings.extra.html',
			'helene.html',
			'helene.get.templates',
			'helene.get.templates.bypath',
			'helene.nls',
			'helene.show.template-block.html',
			'helene.template.menu.html'
		);

		foreach ($templates as $name) {
			$this->assertSame('</pinp>', substr($this->getTemplate($name), -7), $name);
		}
		$this->assertSame('</pinp>', substr($this->getTemplate('helene.nls', 'nl'), -7), 'helene.nls.nl');
	}
}
