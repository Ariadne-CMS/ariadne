#!/usr/bin/env php
<?php

	$ARLoader = 'cmd';
	$currentDir = getcwd();
	$ariadne = dirname($currentDir).'/lib/';

	if (!@include_once($ariadne."/bootstrap.php")) {
		chdir(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
		$ariadne = dirname(getcwd()).'/lib/';

		if(!include_once($ariadne."/bootstrap.php")){
			echo "could not find Ariadne";
			exit(1);
		}

		chdir($currentDir);
	}

	$cmd = $argv[1];
	$arguments = array_slice($argv, 2);

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
			pcntl_exec($cmd,$arguments);
			// this program ends here
			// after exec this program nolonger exists

		}
	}
	exit($return_var);

