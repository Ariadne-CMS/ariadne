#!/usr/local/bin/php -q
<pre>
<?php
	require("../ariadne.inc");
	require($ariadne."/configs/ariadne.phtml");
	require($ariadne."/configs/store.phtml");
	require($ariadne."/configs/axstore.phtml");
	include_once($ariadne."/includes/loader.web.php");
	include_once($ariadne."/stores/".$ax_config["dbms"]."store.phtml");
	include_once($ariadne."/stores/".$store_config["dbms"]."store_install.phtml");
	$ERRMODE="text";


	$inst_store = $store_config["dbms"]."store_install";
	$store=new $inst_store(".",$store_config);
	
	echo "== updating main Ariadne Object Store\n\n";

		$time["ctime"]["number"]=1;
		$time["mtime"]["number"]=1;
		$time["muser"]["string"]=32;
		$time_index[0][0]="ctime";
		$time_index[1][0]="mtime";
		$time_index[2][0]="muser";
		$store->create_property("time", $time, $time_index);

		$owner["owner"]["string"]=32;
		$store->create_property("owner",$owner);

		$custom["name"]["string"]=32;
		$custom["value"]["string"]=128;
		$custom["nls"]["string"]=4;
		$store->create_property("custom",$custom);

	echo "== importing ariadne.ax file\n\n";

		global $options;
		$options["verbose"]=true;
		// become admin
		$AR->user=new object;
		$AR->user->data=new object;
		$AR->user->data->login=$ARLogin="admin";

		$ax_config["writeable"]=false;
		$ax_config["database"]="ariadne.ax";
		echo "ax file (".$ax_config["database"].")\n";
		set_time_limit(0);
		$inst_store = $ax_config["dbms"]."store";
		$axstore=new $inst_store(".", $ax_config);
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
