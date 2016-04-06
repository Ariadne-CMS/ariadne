<?php
	require_once("../ariadne.inc");
	require_once($ariadne . "/bootstrap.php");

	require_once( AriadneBasePath . "/configs/axstore.phtml");
	require_once( AriadneBasePath . "/configs/cache.phtml");
	require_once( AriadneBasePath . "/stores/".$ax_config["dbms"]."store.phtml");
	require_once( AriadneBasePath . "/stores/".$store_config["dbms"]."store_install.phtml");

	$ERRMODE="text";

	$inst_store = $store_config["dbms"]."store_install";
	$store=new $inst_store(".",$store_config);

	echo "== creating main Ariadne Object Store\n\n";

	if ($store->initialize()) {
		require_once("init_database_data.php");

		foreach ($properties as $name => $property) {
			$store->create_property($name, $property);
		}

		$store->add_type("pobject","pobject");
		$store->add_type("pdir","pobject");
		$store->add_type("pdir","ppage");
		$store->add_type("pdir","pdir");
		$store->add_type("pshortcut","pobject");
		$store->add_type("pshortcut","pshortcut");
		$store->add_type("puser","pobject");
		$store->add_type("puser","ppage");
		// $store->add_type("puser","pdir");
		$store->add_type("puser","puser");
		$store->add_type("pshadowuser","pobject");
		$store->add_type("pshadowuser","ppage");
		// $store->add_type("pshadowuser","pdir");
		$store->add_type("pshadowuser","puser");
		$store->add_type("pshadowuser","pshadowuser");
		$store->add_type("pgroup","pobject");
		$store->add_type("pgroup","ppage");
		$store->add_type("pgroup","pdir");
		$store->add_type("pgroup","puser");
		$store->add_type("pgroup","pgroup");
		$store->add_type("ppage","pobject");
		$store->add_type("ppage","ppage");
		$store->add_type("pcalitem","pobject");
		$store->add_type("pcalitem","pcalitem");
		$store->add_type("pcalendar","pobject");
		$store->add_type("pcalendar","ppage");
		$store->add_type("pcalendar","pdir");
		$store->add_type("pcalendar","pcalendar");
		$store->add_type("pscenario","pobject");
		$store->add_type("pscenario","pscenario");
		$store->add_type("particle","pobject");
		$store->add_type("particle","ppage");
		$store->add_type("particle","particle");
		$store->add_type("pnewspaper","pobject");
		$store->add_type("pnewspaper","ppage");
		$store->add_type("pnewspaper","pdir");
		$store->add_type("pnewspaper","pnewspaper");

		// Addressbook types and default objects

		$store->add_type("paddressbook","pobject");
		$store->add_type("paddressbook","ppage");
		$store->add_type("paddressbook","pdir");
		$store->add_type("paddressbook","paddressbook");
		$store->add_type("pperson","pobject");
		$store->add_type("pperson","address");
		$store->add_type("pperson","pperson");
		$store->add_type("porganization","pobject");
		$store->add_type("porganization","address");
		$store->add_type("porganization","porganization");


		// install psite types and properties

		$store->add_type("psite","pobject");
		$store->add_type("psite","ppage");
		$store->add_type("psite","pdir");
		$store->add_type("psite","psection");
		$store->add_type("psite","psite");

		// install pfile type

		$store->add_type("pfile","pobject");
		$store->add_type("pfile","pfile");

		// install pphoto(book) types

		$store->add_type("pphoto","pobject");
		$store->add_type("pphoto","pfile");
		$store->add_type("pphoto","pphoto");
		$store->add_type("pphotobook","pobject");
		$store->add_type("pphotobook","ppage");
		$store->add_type("pphotobook","pdir");
		$store->add_type("pphotobook","pphoto");
		$store->add_type("pphotobook","pphotobook");

		// install pprofile type

		$store->add_type("pprofile","pobject");
		$store->add_type("pprofile","ppage");
		$store->add_type("pprofile","pdir");
		$store->add_type("pprofile","pprofile");

		// install psearch type

		$store->add_type("psearch","pobject");
		$store->add_type("psearch","ppage");
		$store->add_type("psearch","pdir");
		$store->add_type("psearch","psearch");

		// install psection type

		$store->add_type("psection", "pobject");
		$store->add_type("psection", "ppage");
		$store->add_type("psection", "pdir");
		$store->add_type("psection", "psection");

		// install pproject type

		$store->add_type("pproject", "pobject");
		$store->add_type("pproject", "ppage");
		$store->add_type("pproject", "pdir");
		$store->add_type("pproject", "psection");
		$store->add_type("pproject", "pproject");

		// install pconnector type

		$store->add_type("pconnector", "pobject");
		$store->add_type("pconnector", "ppage");
		$store->add_type("pconnector", "pdir");
		$store->add_type("pconnector", "pconnector");

		// install pldapconnection type

		$store->add_type("pldapconnection", "pobject");
		$store->add_type("pldapconnection", "ppage");
		$store->add_type("pldapconnection", "pdir");
		$store->add_type("pldapconnection", "pconnector");
		$store->add_type("pldapconnection", "pldapconnection");

		// install pbookmark type

		$store->add_type("pbookmark", "pobject");
		$store->add_type("pbookmark", "pbookmark");

		// install punittest type
		$store->add_type("punittest", "pobject");
		$store->add_type("punittest", "ppage");
		$store->add_type("punittest", "pdir");
		$store->add_type("punittest", "punittest");

		if ($error) {
			error($error);
		}

	} else {
		error("store not initialized.");
	}
	$store->close();

	// session store

	$inst_store = $session_config["dbms"]."store_install";
	$sessionstore=new $inst_store(".",$session_config);

	echo "== creating Ariadne Session Store\n\n";
	if ($sessionstore->initialize()) {
		$sessionstore->add_type("psession","pobject");
		$sessionstore->add_type("psession","psession");
		$sessionstore->save( '/', 'pobject', new object );
	} else {
		error("store not initialized.");
	}
	$sessionstore->close();

	// cache store

	$inst_store = $cache_config["dbms"]."store_install";
	$cachestore=new $inst_store(".",$cache_config);

	echo "== creating Ariadne Session Store\n\n";
	if ($cachestore->initialize()) {
		foreach ($cacheproperties as $name => $property) {
			$cachestore->create_property($name, $property);
		}
		$cachestore->add_type("pcache","pobject");
		$cachestore->add_type("pcache","pcache");

		$cachestore->save( '/', 'pobject', new object );
	} else {
		error("store not initialized.");
	}
	$cachestore->close();

?>
