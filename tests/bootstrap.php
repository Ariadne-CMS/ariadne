<?php

	$ariadne = getcwd().'/lib/';
	$loaderType = 'cmd';
	require_once($ariadne.'/configs/ariadne.phtml');
	require_once($ariadne."/configs/store.phtml");
	require_once($ariadne."/includes/loader.".$loaderType.".php");
	require_once($ariadne."/stores/".$store_config["dbms"]."store.phtml");
	include($ariadne."/nls/".$AR->nls->default);
	require_once($ariadne."/ar.php");

?>