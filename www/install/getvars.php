<?php
	include_once("system_checks.php");

	function getPostVar( $name ) {
		return ( isset( $_POST[$name] ) ? $_POST[$name] : null );
	}

	$defaults = array(
		"language" 	=> "en",
		"step" 		=> "step1",
		"database_host"	=> "localhost"
	);

	if (check_svn() && check_svn_write()) {
		$defaults['enable_svn'] = 1;
	} else {
		$defaults['enable_svn'] = 0;
	}

	$languages = array(
		"en" => "English",
		"nl" => "Nederlands"
	);

	$steps = array(
		"step1" => "step1.php",
		"step2" => "step2.php",
		"step3" => "step3.php",
		"step4" => "step4.php",
		"step5" => "step5.php",
		"step6" => "step6.php",
		"step7" => "login.php"
	);

	$databases = array();
	if (check_mysql()) {
		$databases['mysql'] = "MySQL";
//		$databases['mysql_workspaces'] = "MySQL Workspaced (EXPERIMENTAL)";
	}
	if (check_postgresql()) {
		$databases['postgresql'] = "PostgreSQL";
	}
	if (check_sqlite()) {
		$databases['sqlite'] = "SQLite (EXPERIMENTAL)";
	}

	$language            = getPostVar('language');
	$step                = getPostVar('step');
	$database            = getPostVar('database');
	$database_host       = getPostVar('database_host');
	$database_user       = getPostVar('database_user');
	$database_pass       = getPostVar('database_pass');
	$database_name       = getPostVar('database_name');
	$admin_pass          = getPostVar('admin_pass');
	$admin_pass_repeat   = getPostVar('admin_pass_repeat');
	$ariadne_location    = getPostVar('ariadne_location');
	$install_demo        = getPostVar('install_demo');
	$install_libs        = getPostVar('install_libs');
	$install_docs        = getPostVar('install_docs');
	$enable_svn          = getPostVar('enable_svn');
	$enable_workspaces   = getPostVar('enable_workspaces');
	$downloaded_config   = getPostVar('downloaded_config');

	// Sanity checks for postvars, make sure the values are what we expect.
	if ( !isset($languages[$language] ) ) {
		$language = $defaults['language'];
	}

	if ( !isset($steps[$step]) ) {
		$step = $defaults['step'];
	}

	if ( !isset($databases[$database]) ) {
		$database = '';
	}

	if (!isset($database_host)) {
		$database_host = $defaults['database_host'];
	}

	if (!isset($enable_svn)) {
		$enable_svn = $defaults['enable_svn'];
	}

	// Add the vars here that will be fed to keepvars.php; These will be passed from step to step.
	$postvars = array();
	$postvars['language']          = $language;
	$postvars['step']              = $step;
	$postvars['database']          = $database;
	$postvars['database_host']     = $database_host;
	$postvars['database_user']     = $database_user;
	$postvars['database_pass']     = $database_pass;
	$postvars['database_name']     = $database_name;
	$postvars['admin_pass']        = $admin_pass;
	$postvars['admin_pass_repeat'] = $admin_pass_repeat;
	$postvars['admin_pass_repeat'] = $admin_pass_repeat;
	$postvars['ariadne_location']  = $ariadne_location;
	$postvars['enable_svn']        = $enable_svn;
	$postvars['enable_workspaces'] = $enable_workspaces;
	$postvars['install_demo']      = $install_demo;
	$postvars['install_libs']      = $install_libs;
	$postvars['install_docs']      = $install_docs;

	$postvars = array_merge($postvars,$found_bins);

	if ($step == 'step6') {
		$postvars['downloaded_config'] = $downloaded_config;
	}
?>
