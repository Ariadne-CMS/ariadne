<?php
	error_reporting( E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT) );
	require_once("./../../vendor/autoload.php");
	include_once("getvars.php");
	include($steps[$step]);
?>
