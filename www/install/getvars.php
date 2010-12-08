<?php
	include_once("system_checks.php");

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
	}
	if (check_postgresql()) {
		$databases['postgresql'] = "PostgreSQL";
	}

	$language            = $_POST['language'];
	$step                = $_POST['step'];
	$database            = $_POST['database'];
	$database_host       = $_POST['database_host'];
	$database_user       = $_POST['database_user'];
	$database_pass       = $_POST['database_pass'];
	$database_name       = $_POST['database_name'];
	$admin_pass          = $_POST['admin_pass'];
	$admin_pass_repeat   = $_POST['admin_pass_repeat'];
	$ariadne_location    = $_POST['ariadne_location'];
	$install_demo        = $_POST['install_demo'];
	$install_libs        = $_POST['install_libs'];
	$install_docs        = $_POST['install_docs'];
	$enable_svn          = $_POST['enable_svn'];
	$downloaded_config   = $_POST['downloaded_config'];

	// Sanity checks for postvars, make sure the values are what we expect.
	if (!$languages[$language]) {
		$language = $defaults['language'];
	}

	if (!$steps[$step]) {
		$step = $defaults['step'];
	}

	if (!$databases[$database]) {
		$database = '';
	}

	if (!$database_host) {
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
	$postvars['install_demo']      = $install_demo;
	$postvars['install_libs']      = $install_libs;
	$postvars['install_docs']      = $install_docs;

	$postvars = array_merge($postvars,$found_bins);

	if ($step == 'step6') {
		$postvars['downloaded_config'] = $downloaded_config;
	}
?>
