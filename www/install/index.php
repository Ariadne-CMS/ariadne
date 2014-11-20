<?php
	error_reporting( E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT) );
	include_once("getvars.php");
	include($steps[$step]);
?>
