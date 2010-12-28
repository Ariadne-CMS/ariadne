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

	$ERRMODE="text";

	$inst_store = $store_config["dbms"]."store_install";
	$store=new $inst_store(".",$store_config);
	
	echo "== creating main Ariadne Object Store\n\n";

	if ($store->initialize()) {

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

		$state["value"]["string"]=16;
		$state["group"]["string"]=16;
		$state["operator"]["string"]=128;
		$state_index[0][0]="value";
		$state_index[1][0]="group";
		$state_index[2][0]="operator"; 

		$store->create_property("state", $state, $state_index);

		$name["value"]["string"]=128;
		$name["nls"]["string"]=4;
		$store->create_property("name", $name);

		$value["value"]["string"]=128;
		$store->create_property("value",$value);

		$ptext["value"]["string"]=128;
		$ptext["nls"]["string"]=4;
		$store->create_property("text",$ptext);

		$locked["id"]["string"]=32;
		$locked["duration"]["number"]=1;
		$store->create_property("locked", $locked);

		$login["value"]["string"]=32;
		$store->create_property("login", $login);

		$members["login"]["string"]=32;
		$store->create_property("members", $members);

		$time["ctime"]["number"]=1;
		$time["mtime"]["number"]=1;
		$time["muser"]["string"]=32;
		$time_index[0][0]="ctime";
		$time_index[1][0]="mtime";
		$time_index[2][0]="muser";
		$store->create_property("time", $time, $time_index);

		$owner["value"]["string"]=32;
		$store->create_property("owner",$owner);

		$custom["name"]["string"]=32;
		$custom["value"]["string"]=128;
		$custom["nls"]["string"]=4;
		$store->create_property("custom",$custom);

		// now install all pcalendar and pcalitem objects.

		$timeframe["start"]["number"]=1;
		$timeframe["end"]["number"]=1;
		$store->create_property("timeframe", $timeframe);

		$priority["value"]["number"]=1;
		$store->create_property("priority", $priority);

		$store->add_type("pcalitem","pobject");
		$store->add_type("pcalitem","pcalitem");
		$store->add_type("pcalendar","pobject");
		$store->add_type("pcalendar","ppage");
		$store->add_type("pcalendar","pdir");
		$store->add_type("pcalendar","pcalendar");

		// newspaper types and default objects

		$article["start"]["number"]=1;
		$article["end"]["number"]=1;
		$article["display"]["string"]=50;
		$store->create_property("article", $article);

		$published["value"]["number"]=1;
		$store->create_property("published",$published);

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

		$address["street"]["string"]=50;
		$address["zipcode"]["string"]=6;
		$address["city"]["string"]=50;
		$address["state"]["string"]=50;
		$address["country"]["string"]=50;
		$address_index[0][0]="city";
		$address_index[0][1]="street";
		$address_index[1][0]="zipcode";
		$address_index[2][0]="country";
		$address_index[2][1]="state";
		$store->create_property("address", $address, $address_index);

		// install psite types and properties

		$store->add_type("psite","pobject");
		$store->add_type("psite","ppage");
		$store->add_type("psite","pdir");
		$store->add_type("psite","psection");
		$store->add_type("psite","psite");

		$url["host"]["string"]=50;
		$url["port"]["number"]=1;
		$url["protocol"]["string"]=10;
		$store->create_property("url", $url);

		// install pfile type

		$store->add_type("pfile","pobject");
		$store->add_type("pfile","pfile");
		$mimetype["type"]["string"] = 20;
		$mimetype["subtype"]["string"] = 20;
		$store->create_property("mimetype", $mimetype);

		// install pphoto(book) types

		$store->add_type("pphoto","pobject");
		$store->add_type("pphoto","pfile");
		$store->add_type("pphoto","pphoto");
		$store->add_type("pphotobook","pobject");
		$store->add_type("pphotobook","ppage");
		$store->add_type("pphotobook","pdir");
		$store->add_type("pphotobook","pphoto");
		$store->add_type("pphotobook","pphotobook");

		// create fulltext property (if fulltext search is supported)
		if ($store->is_supported("fulltext")) {
			$fulltext["value"]["text"] = 1;
			$fulltext["nls"]["string"] = 4;
			$store->create_property("fulltext", $fulltext);
		}


		// create references property
		$references["path"]["string"] = 128;
		$store->create_property("references", $references);

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

	} else {
		error("store not initialized.");
	}
	$sessionstore->close();
	
?>
