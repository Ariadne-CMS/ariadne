#!/usr/local/bin/php -q
<pre>
<?php
	require("../ariadne.inc");
	require($ariadne."/configs/ariadne.phtml");
	require($ariadne."/configs/store.phtml");
	require($ariadne."/configs/axstore.phtml");
	include_once($store_config['code']."includes/loader.web.php");
	include_once($store_config['code']."stores/".$ax_config["dbms"]."store.phtml");
	include_once($store_config['code']."stores/".$store_config["dbms"]."store_install.phtml");
        include_once($store_config['code']."nls/".$AR->nls->default);
	$ERRMODE="text";


	echo "== upgrading ariadne\n\n";

	$inst_store = $store_config["dbms"]."store_install";
	$store=new $inst_store(".",$store_config);

		// add missing mimetype property table
		$mimetype["type"]["string"] = 20;
		$mimetype["subtype"]["string"] = 20;
		$store->create_property("mimetype", $mimetype);

		// install pprofile type
		
		$store->add_type("pprofile","pobject");
		$store->add_type("pprofile","pdir");
		$store->add_type("pprofile","ppage");
		$store->add_type("pprofile","pprofile");

		echo "== importing upgrade.ax file\n\n";

		$ARCurrent->options["verbose"]=true;
		// become admin
		$AR->user=new object;
		$AR->user->data=new object;
		$AR->user->data->login=$ARLogin="admin";

		$ax_config["writeable"]=false;
		$ax_config["database"]="upgrade.ax";
		echo "ax file (".$ax_config["database"].")\n";
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

		if ($error) {
			error($error);
		}

	$store->close(); 

	
?>
</pre>