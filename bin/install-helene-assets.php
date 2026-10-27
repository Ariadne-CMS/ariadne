#!/usr/bin/env php
<?php
	// Run as the web-server user from the installed site's bin directory.
	// Filestore writes use owner-only permissions; root-owned files break web requests.
	if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
		fwrite(STDERR, "Run this installer as the web-server user (for example www-data), not root.\n");
		exit(1);
	}
	$ARLoader = 'cmd';
	$ariadne = dirname(getcwd()).'/lib/';
	require $ariadne.'bootstrap.php';
	require_once $ariadne.'modules/mod_pinp.phtml';
	set_include_path(dirname(__DIR__).'/vendor/pear/pear-core-minimal/src'.PATH_SEPARATOR.get_include_path());
	require_once dirname(__DIR__).'/vendor/pear/archive_tar/Archive/Tar.php';

	$class = $store_config['dbms'].'store';
	$store = new $class($root ?? null, $store_config);
	$AR->user = current($store->call('system.get.phtml', array(), $store->get('/system/users/admin/')));
	if (!$AR->user) {
		throw new RuntimeException('Cannot load the admin user');
	}
	$ARCurrent->allnls = true;
	$ARCurrent->nolangcheck = true;
	$library = current($store->call('system.get.phtml', array(), $store->get('/system/lib/muze/dev/helene/')));
	if (!$library) {
		throw new RuntimeException('The Helene library is not installed');
	}

	$archive = new Archive_Tar(dirname(__DIR__).'/www/install/packages/lib.ax', 'gz');
	$manifestSource = $archive->extractInString('./templates/33/_pobject.helene.assets.json.any.pinp');
	if (!is_string($manifestSource) || strpos($manifestSource, '</pinp>') === false) {
		throw new RuntimeException('The Helene asset manifest is missing');
	}
	$manifest = json_decode(substr($manifestSource, strpos($manifestSource, '</pinp>') + 7), true, 512, JSON_THROW_ON_ERROR);
	$sources = array();
	foreach ($manifest['templates'] as $name) {
		$source = $archive->extractInString('./templates/33/_pobject.'.$name.'.any.pinp');
		if (!is_string($source)) {
			throw new RuntimeException('Missing packaged template: '.$name);
		}
		$compiler = new pinp($AR->PINP_Functions, 'local->', '$AR_this->_');
		$compiled = $compiler->compile(str_replace("\r", '', $source));
		if ($compiler->error) {
			throw new RuntimeException($name.': '.$compiler->error);
		}
		token_get_all(sprintf($AR->PINPtemplate, $compiled), TOKEN_PARSE);
		$sources[$name] = $source;
	}

	foreach ($sources as $name => $source) {
		$library->call('system.save.layout.phtml', array(
			'template' => $source,
			'type' => 'pobject',
			'function' => $name,
			'language' => 'any',
			'default' => 1,
			'private' => false
		));
		if ($library->error) {
			throw new RuntimeException($name.': '.$library->error);
		}
		$templates = $store->get_filestore('templates');
		if ($templates->read($library->id, 'pobject.'.$name.'.any.pinp') !== $source) {
			throw new RuntimeException('Installed template differs from package: '.$name);
		}
		echo 'Installed '.$name."\n";
	}
	$store->close();
