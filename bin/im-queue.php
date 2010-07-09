#!/usr/bin/php -q
<?php
	$ariadne = "../lib";
	if (!@include_once($ariadne."/configs/ariadne.phtml")) {
		chdir(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
		if(!include_once($ariadne."/configs/ariadne.phtml")){
			echo "could not open ariadne.phtml";
			exit(1);
		}
	}

	$cmd = $argv[1];
	$cmd_args = '';
	$arguments = array_slice($argv, 2);
	foreach ($arguments as $i => $arg) {
		$cmd_args .= ' '.escapeshellarg($arg);
	}

	$semKey = $AR->IMQueue['semKey'];
	if (!$semKey) {
		$semKey = 1234;
	}
	$max    = $AR->IMQueue['max'];
	if (!$max) {
		$max = 2;
	}

	$sem = sem_get($semKey, $max);
	sem_acquire($sem);
		$p = popen($cmd.$cmd_args, 'r');
		while (!feof($p)) {
			echo fread($p, 4096);
		}
		fclose($p);
	sem_release($sem);

?>