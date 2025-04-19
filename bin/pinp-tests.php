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

	$inst_store = $store_config["dbms"]."store";
	$store = new $inst_store( "/", $store_config );

	/* now load a user (admin in this case)*/
	$login = "admin";
	$query = "object.implements = 'puser' and login.value='$login'";
	$AR->user = current($store->call('system.get.phtml', '', $store->find('/system/users/', $query)));


	$tests_dir = $currentDir . "/pinp-tests/";
	require_once( $tests_dir . "lib/compile.templates.php" );

	$options = [];
	$arg_c = 0;
	$arg_v = [];
	$arg_v[ $arg_c++ ] = $argv[ 0 ];

	foreach ( $argv as $i => $cmd ) {
		if ( $i === 0 ) {
			continue;
		}
		$option = ldParseOption( $cmd );
		if ($option !== false ) {
			$showHelp = false;
			switch ( $option[ "switch" ] ) {
				case "create":
					switch ( $option[ "subswitch" ] ) {
						case "cmp-files":
							$value = strtolower( trim( $option[ "value" ] ) );
							if ( in_array( $value, [ "0", "false", "no" ] ) ) {
								$options[ "create-cmp-files" ] = "N";
								continue 3;
							}
							if ( in_array( $value, [ "1", "true", "yes" ] ) ) {
								$options[ "create-cmp-files" ] = "Y";
								continue 3;
							}
							echo "Please enter [0/false/no] or [1/true/yes] for --create-cmp-files=\n";
							exit( 1 );
						break;
					}
				break;
				default:
				case "help":
					$showHelp = true;
				break;
			}
			if ( $showHelp ) {
				echo "\n";
				echo "pinp-tests [--help] [--create-cmp-files=1/true/yes/0/false/no]\n";
				echo "\n";
				echo "\tThe pinp-tests command will compile all *.pinp files\n";
				echo "\tfound within the pinp-tests/ directory.\n";
				echo "\tOn failing to compile a file it will print a detailed error\n";
				echo "\tWhen a file has been succesfully compiled its php code will\n";
				echo "\tbe compared to the corresponding .cmp file.\n";
				echo "\tIn case a .cmp file does not exist the user will be asked if\n";
				echo "\tif the command has to create one (with the resulting php code).\n";
				echo "\tThe user prompt can be skipped by using the --create-cmp-files\n";
				echo "\tparameter.\n";
				echo "\tWhen the resulting php code differs from the contents of the\n";
				echo "\tcorresponding .cmp file the command will print a diff -u between\n";
				echo "\tthe contents of the .cmp file and the resulting code.\n";
				echo "\n";
				exit( 0 );
			}
		} else {
			$arg_v[ $arg_c++ ] = $cmd;
		}
	}

	$files = [];
	$dh = opendir($tests_dir);
	while ( false !== ($file = readdir($dh))) {
		if ($file != "." && $file != "..") {
			$f = $tests_dir.$file;
			if (substr($file, -strlen(".pinp")) == ".pinp" && is_file($f)) {
				array_push( $files, $f );
			}
		}
	}
	$retVal = 0;
	foreach ( $files as $f ) {
		echo "Compiling $f\n";
		$compilerError = null;
		$fname = basename( $f );
		$file_cmp = substr($f, 0, -strlen(".pinp") ) . ".cmp";
		$result = compileTemplate( $f, $compilerError );
		if ( $compilerError !== null ) {
			echo "Failed to compile $f. See error message for details\n";
			$retVal = 1;
			continue;
		}
		$do_create = false;
		if ( !is_file( $file_cmp ) ) {
			while ( true ) {
				if ( isset( $options[ 'create-cmp-files' ] ) ) {
					$do_create = $options[ 'create-cmp-files' ];
				}
				if ( !$do_create ) {
					$do_create = strtoupper( trim( readline( "No cmp file found for $fname. Would you like me to create it? [Y/n]\n" ) ) );
				}
				if ( !strlen( $do_create ) || $do_create === "Y" ) {
					file_put_contents( $file_cmp, $result );
					echo "Compare file has been created.\n";
					continue 2;
				}
				if ( $do_create === "N" ) {
					echo "Skipping comparison for $fname.\n";
					continue 2;
				}
			}
		}
		$data_cmp = file_get_contents( $file_cmp );
		if ( $data_cmp !== $result ) {
			$retVal = 1;
			echo "There is a difference in output for $fname:\n";
			echo getDiff( $file_cmp, $result );
		}
	}

	$store->close();
	exit( $retVal );
?>