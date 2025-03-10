<?php
	error_reporting( E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT) );
	@include('./../ariadne.inc');
	if(!($ariadne ?? null)) {
		$ariadne = realpath(__DIR__ . '/../');
	}
	$autoload = $ariadne."/../vendor/autoload.php";
	if(file_exists($autoload)){
		require_once($autoload);
	}
	include_once("getvars.php");
	include($steps[$step]);

?>
