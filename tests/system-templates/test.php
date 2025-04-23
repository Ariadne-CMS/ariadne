<?php
	$ariadne = __DIR__ . "lib/";
	
	require_once($ariadne."/stores/store.php");
	include_once($ariadne."/bootstrap.php");
	
	class teststore extends store {
	}
	
	$ARLoader = 'cmd';
	$store = new teststore($root, $store_config);

	$AR->user = $store->newobject();
	$testObject = $store->newobject();
	
	$result = $testObject->call("system.get.phtml");
	var_dump($result);
