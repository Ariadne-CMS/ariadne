<?php
	// make sure this page is never cached, that creates problems with
	// logging in/out of ariadne.
	Header("Cache-control: no-cache");
	Header("Expires: ".gmdate(DATE_RFC1123));
	// load /system/login.html which will do the job
	if (file_exists("ariadne.inc") && is_readable("ariadne.inc")) {
		include("ariadne.inc");
		if (file_exists($ariadne . "/configs/ariadne.phtml") && is_readable($ariadne . "/configs/ariadne.phtml")) {
			include_once($ariadne . "/bootstrap.php");
		}
	}
	if (!$AR) {
		if (file_exists("install") && is_readable("install")) {
			Header("Location: install/");
		}
	} else {
		// All is well.
		$AR_PATH_INFO="/system/ariadne.html";
		$_SERVER["PATH_INFO"]="/system/ariadne.html"; // backwards compatible for old loaders, just to be sure
		include("./loader.php");
	}
?>
