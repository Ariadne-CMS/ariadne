<?php
	$ariadne = '';
	if ( isset($new_admin_password) ) {
		include("../ariadne.inc");
		include_once($ariadne."/configs/ariadne.phtml");
		include_once($ariadne."/configs/store.phtml");
		include_once($ariadne."/stores/".$store_config["dbms"]."store.phtml");
		include_once($ariadne."/includes/loader.web.php");
		include_once($ariadne."/modules/mod_ar.php");

		/* become admin */
		$ARLogin="admin";
		$AR->user=new object;
		$AR->user->data=new object;
		$AR->user->data->login="admin";

		$inst_store = $store_config["dbms"]."store";
		$store=new $inst_store(".",$store_config);

		global $ARCurrent;
		$ARCurrent->nolangcheck = true;
		$ARCurrent->allnls = true;

		/* update the admin user with the supplied password */
		$store->call(
			"system.save.data.phtml",
			Array(
				"newpass1" => $new_admin_password,
				"newpass2" => $new_admin_password
			),
			$store->get("/system/users/admin/")
		);

		$store->close();
	} else {
		echo 'no password entered';
	}
?>