<?php
	include_once("../ariadne.inc");
	require_once($ariadne."/configs/ariadne.phtml");
	require_once($ariadne."/configs/store.phtml");
	require_once($ariadne."/configs/sessions.phtml");
	require_once($ariadne."/configs/axstore.phtml");
	include_once( $store_config['code']."includes/loader.web.php" );
	include_once( $store_config['code']."stores/".$ax_config["dbms"]."store.phtml");
	include_once( $store_config['code']."stores/".$store_config["dbms"]."store_install.phtml");
	include_once( $store_config['code']."nls/".$AR->nls->default );
	include_once($ariadne."/ar.php");
	include_once($ariadne."/modules/mod_cache.php");
	include($ariadne."/version.php");

	/* instantiate the store */
	$inst_store = $store_config["dbms"]."store";
	$store = new $inst_store($root,$store_config);

//	echo "== importing base.ax file\n\n";
	$ARCurrent->nolangcheck = true;
	$ARCurrent->options["verbose"]=true;
	// become admin
	$AR->user=new baseObject;
	$AR->user->data=new baseObject;
	$AR->user->data->login=$ARLogin="admin";

	$ax_config["writeable"]=false;
	$ax_config["database"]="packages/base.ax";
//	echo "ax file (".$ax_config["database"].")\n";
	set_time_limit(0);
	$inst_store = $ax_config["dbms"]."store";
	$axstore=new $inst_store("", $ax_config);
	if (!$axstore->error) {
		$ARCurrent->importStore=&$store;
		$args="srcpath=/&destpath=/";
		$axstore->call("system.export.phtml", $args,
			$axstore->get("/"));
		$error=$axstore->error;
		$axstore->close();
	} else {
		$error=$axstore->error;
	}

	$version = $ARversion['version'];
	ar::get('/system/ariadne/version/')->call('system.save.data.phtml', array('value' => $version));


	$ax_config["writeable"]=false;
	$ax_config["database"]="packages/lib.ax";
	set_time_limit(0);
	$inst_store = $ax_config["dbms"]."store";
	$axstore=new $inst_store("", $ax_config);
	if (!$axstore->error) {
		$ARCurrent->importStore=&$store;
		$args="srcpath=/&destpath=/";
		$axstore->call("system.export.phtml", $args,
			$axstore->get("/"));
		$error=$axstore->error;
		$axstore->close();
	} else {
		$error=$axstore->error;
		echo $error;
	}

	$store->close();
?>
