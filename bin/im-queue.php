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
		$semKey = ftok('/',1);
	}

	$max    = $AR->IMQueue['max'];
	if (!$max) {
		$max = 2;
	}

	$timeout    = $AR->IMQueue['timeout'];
	if (!$timeout) {
		$timeout = 60;
	}

	/*
	from the sysexit.h : #define EX_UNAVAILABLE  69      // service unavailable 
	*/
	$return_var = 69;

	if( $sem = sem_get($semKey, $max)) {
		pcntl_alarm($timeout); // we wait timeout seconds after that, lets bail out
		if ( sem_acquire($sem) ) {
			pcntl_alarm(0); // we have the lock, now we wait till the programm ends
			passthru  (  $cmd.$cmd_args  ,&$return_var  );
		}
		sem_release($sem);
	}
	exit($return_var);

?>
