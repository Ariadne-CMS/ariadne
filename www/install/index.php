<?php
	error_reporting( E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT) );
	require_once("./../../vendor/autoload.php");
	include_once("getvars.php");
	include($steps[$step]);

	// declare default object,
	if (!class_exists('object',false)) {
		class object { }           // do not change
	}

?>
