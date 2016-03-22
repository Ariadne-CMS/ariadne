<?php
	/******************************************************************
	upgrade.php                                           Muze Ariadne

	depends on de availability of $old_version to generate the list of tasks

	******************************************************************/



	$todo = array();
	switch ($old_version) {
		default:
		break;
		case "2.2":
		case "2.2.1":
		case "2.2.2":
			array_push($todo, array(
								"description" => "Installing grant names for the new grants dialog.",
								"operation" => "2.2/upgrade.grants.php",
								"newversion" => "2.4rc1"
								));

		case "2.4rc1":
			if($store_config["dbms"] == "postgresql") {
				$newversion = "2.4rc2.1";
			} else {
				$newversion = "2.4rc2.2";
			}
			array_push($todo, array(
								"description" => "Moving configuration into data->config.",
								"operation" => "2.4rc1/upgrade.configdata.php",
								"newversion" => $newversion
								));
		case "2.4rc2.1":
			if($store_config["dbms"] == "postgresql") {
				array_push($todo, array(
							"description" => "updating the postgresql store.",
							"operation" => "2.4rc2/upgrade.postgresql.lowercase.php",
							"newversion" => "2.4rc2.2"
							));
			}

		case "2.4rc2.2":
				array_push($todo, array(
							"description" => "updating the Ariadne types install.",
							"operation" => "2.4rc2/upgrade.types.php",
							"newversion" => "2.4"
							));

		case "2.4":
				array_push($todo, array(
							"description" => "correcting nls names for es.",
							"operation" => "2.4/upgrade.nls.es.php",
							"newversion" => "2.4.0.1"
							));
		case "2.4.0.1":
				array_push($todo, array(
							"description" => "Recompiling all PINP templates.",
							"operation" => "all/upgrade.templates.php",
							"newversion" => "2.6"
							));
		case "2.4.1":
		case "2.6":
		case "2.6.0":
		case "2.6.1":
		case "2.6.1-php4":
		case "2.7":
		case "2.7.0":
		case "2.7.1":
		case "2.7.2":
		case "2.7.3":
				array_push($todo, array(
							"description" => "Adding the pproject type.",
							"operation" => "2.7.4/upgrade.types.php",
							"newversion" => "2.7.4"
							));
		case "2.7.4":
			if (in_array($store_config["dbms"], array("mysql", "mysql4"))) {
				array_push($todo, array(
							"description" => "Adding defaults to store_prop definitions.",
							"operation" => "2.7.5/upgrade.store_prop_tables.php",
							"newversion" => "2.7.5pre1"
							));
			}
		case "2.7.5pre1":
		case "2.7.5":
		case "2.7.6":
		case "2.7.7":
		case "2.7.8":
		case "2.7.9":
		case "8.0rc1":
				array_push($todo, array(
							"description" => "Recompiling all PINP templates.",
							"operation" => "all/upgrade.templates.php",
							"newversion" => "8.0rc2"
							));
		case "8.0rc2":
				array_push($todo, array(
							"description" => "Remove hardlinks for users under groups",
							"operation" => "8.0/convert-hardlink-to-shortcuts.php",
							"newversion" => "8.0"
							));
		case "8.0":
		case "8.1":
		case "8.2":
		case '8.3':
				array_push($todo, array(
							"description" => "Remove duplicate content in filestore by removing the non-nls version of files",
							"operation" => "8.4/upgrade.files.php",
							"newversion" => "9.0-rc1.1"
							));
		/*
		case '8.4-b1':
				array_push($todo, array(
							"description" => "Bumping revision to 8.4",
							"operation" => "all/dummy.php",
							"newversion" => "8.4"
							));
		*/
		case '8.4-b1': // because of previous released upgrade scripts
		case '8.4-b2': // because of previous released upgrade scripts
		case '8.4':
		case '9.0-rc1.1':
				array_push($todo, array(
							"description" => "Installing cache store",
							"operation" => "9.0/install.cache_store.php",
							"newversion" => '9.0-rc1.1a'
						));
		case '9.0-rc1.1a':
				array_push($todo, array(
							"description" => "Update properties and path length",
							"operation" => "9.1/upgrade.database.php",
							"newversion" => '9.0-rc1.2'
						));
		case '9.0-rc1.2':
				array_push($todo, array(
							"description" => "Installing default libs",
							"operation" => "9.0/install.muze.libs.php",
							"newversion" => '9.0-rc1.3'
						));
		case '9.0-rc1.3':
		case '9.0-rc1.4':
				array_push($todo, array(
							"description" => "Mogrify dirs in system folder",
							"operation" => "9.0/mogrify.system.folders.php",
							"newversion" => '9.0-rc1.5'
						));
		case '9.0-rc1.5':
				array_push($todo, array(
							"description" => "Mogrify dirs in system folder",
							"operation" => "9.0/install.configfiles.php",
							"newversion" => '9.0-rc1'
						));
		case '9.0-rc1.6':
		case '9.0-rc1.7': // because of previous released upgrade scripts
		case '9.0-rc1':
				array_push($todo, array(
							"description" => "Installing missing objects",
							"operation" => "9.0/install.missing.data.php",
							"newversion" => "9.0"
							));
		case '9.0-rc2.1':
		case '9.0':
				array_push($todo, array(
							"description" => "Updating properties with scope, adding new properties",
							"operation" => "9.1/upgrade.database.php",
							"newversion" => "9.1-rc1.1"
							));
		case '9.1-rc1.1':
		case '9.1-rc1.2':
		case '9.1':
		case '9.2':
				array_push($todo, array(
							"description" => "Updating properties, adding new properties",
							"operation" => "9.1/upgrade.database.php",
							"newversion" => "9.3-rc1"
							));
		case '9.3-rc1':
				array_push($todo, array(
							"description" => "Update libraries for muze and vedor",
							"operation" => "all/upgrade.muze.libs.php",
							"newversion" => "9.4"
						));
		case '9.3':
				array_push($todo, array(
							"description" => "Bumping revision to 9.4",
							"operation" => "all/dummy.php",
							"newversion" => "9.4"
						));
	
	}
