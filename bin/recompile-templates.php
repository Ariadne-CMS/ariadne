#!/usr/bin/env php
<?php

	$ARLoader = 'cmd';
	$currentDir = getcwd();
	$ariadne = dirname($currentDir).'/lib/';

	if (!@include_once($ariadne."/bootstrap.php")) {
		chdir(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
		$ariadne = dirname(getcwd()).'/lib/';

		if(!include_once($ariadne."/bootstrap.php")){
			echo "could not find Ariadne";
			exit(1);
		}

		chdir($currentDir);
	}

	$inst_store = $store_config["dbms"]."store";
	$store=new $inst_store($root??null,$store_config);

	/* now load a user (admin in this case)*/
	$login = "admin";
	$query = "object.implements = 'puser' and login.value='$login'";
	$AR->user = current($store->call('system.get.phtml', '', $store->find('/system/users/', $query)));

	include_once($ariadne."/../www/install/upgrade/all/upgrade.templates.php");
	$store->close();
