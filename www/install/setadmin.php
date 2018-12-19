<?php
	$password = "muze";
	include("../ariadne.inc");
	include($ariadne."/configs/ariadne.phtml");
	include($ariadne."/configs/store.phtml");
	include_once($ariadne."/stores/".$store_config["dbms"]."store.phtml");
	include_once($ariadne."/includes/loader.web.php");
	include_once($ariadne."/ar.php");


	/* become admin */
	$ARLogin="admin";
	$AR->user=new baseObject;
	$AR->user->data=new baseObject;
	$AR->user->data->login="admin";

	$inst_store = $store_config["dbms"]."store";
    $store=new $inst_store(".",$store_config);

	/* update the admin user with the supplied password */
	$store->call("system.save.data.phtml",
					Array(
						"newpass1" => $password,
						"newpass2" => $password
					), $store->get("/system/users/admin/"));
	$store->close();
	echo "You should now be able to log on";
?>
