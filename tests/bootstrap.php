<?php

	global $store_config,$AR,$ariadne,$ax_config,$ARCurrent,$ARConfig,$ARLoader;
	$ariadne = getcwd().'/lib/';
	$ARLoader = 'cmd';

	require_once($ariadne.'/bootstrap.php');
	require_once(AriadneBasePath .'/configs/axstore.phtml');
	require_once(AriadneBasePath .'/stores/axstore.phtml');

	function importContent($base, $package) {
		global $AR,$ARCurrent,$store_config,$ax_config;

		print "Importing $package onto $base\n";

		/* instantiate the store */
		$storetype = $store_config["dbms"]."store";
		$store = new $storetype($root,$store_config);

		$ARCurrent->nolangcheck = true;

		// become admin
		$AR->user=new object;
		$AR->user->data=new object;
		$AR->user->data->login=$ARLogin="admin";

		$ax_config["writeable"]=false;
		$ax_config["database"]=$package;
		set_time_limit(300);
		$storetype = $ax_config["dbms"]."store";
		$axstore=new $storetype("", $ax_config);
		if (!$axstore->error) {
			$ARCurrent->importStore=&$store;
			$args="srcpath=/&destpath=".$base;
			$axstore->call("system.export.phtml", $args,
				$axstore->get("/"));
			$error=$axstore->error;
			$axstore->close();
		} else {
			$error=$axstore->error;
		}

		$store->close();
		print $error;
		return $error;
	}

	function initTestData() {
		global $AR,$ARCurrent,$store_config,$store,$ARConfig;

		$origAR        = clone $AR;
		$origARCurrent = clone $ARCurrent;
		$origARConfig  = clone $ARConfig;

		// become admin
		$AR->user=new object;
		$AR->user->data=new object;
		$AR->user->data->login=$ARLogin="admin";

		/* instantiate the store */
		$storetype = $store_config["dbms"]."store";
		$store = new $storetype($root,$store_config);
		$res = ar::get('/projects/')->call('system.new.phtml', array (
				'arNewType' => 'pproject',
				'arNewFilename' => '/projects/{5:id}',
				'en' => array (
					'name' => 'Unit test dir (en)'.date(DATE_W3C)
				),
				'nl' => array (
					'name' => 'Unit test dir (nl)'.date(DATE_W3C)
				),
				'de' => array (
					'name' => 'Unit test dir (de)'.date(DATE_W3C)
				)
			)
		);
		$base = current($res);

		define('TESTBASE',$base);

		foreach( scandir(getcwd().'/tests/unit/')  as $entry ){
			if(substr($entry,-3) === '.ax' ) {
				importContent($base,getcwd().'/tests/unit/'.$entry);
			}
		}
		importContent($base,getcwd().'/www/install/packages/demo.ax');

		$AR        = $origAR;
		$ARCurrent = $origARCurrent;
		$ARConfig  = $origARConfig;
		
	}

	initTestData();