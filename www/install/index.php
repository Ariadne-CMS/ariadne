<?php
error_reporting( E_ALL ^ E_NOTICE ^ E_DEPRECATED );

	include_once("getvars.php");
	include($steps[$step]);
?>