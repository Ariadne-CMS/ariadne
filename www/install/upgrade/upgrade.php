<?php
    /******************************************************************
     upgrade.php                                           Muze Ariadne
     ------------------------------------------------------------------
     Author: Muze (info@muze.nl)
     Date: 26 october 2004

     Copyright 2004 Muze

     This file is part of Ariadne.

     Ariadne is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published
     by the Free Software Foundation; either version 2 of the License,
     or (at your option) any later version.

     Ariadne is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with Ariadne; if not, write to the Free Software
     Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
     02111-1307  USA

    -------------------------------------------------------------------

     Description:

	This script will bring your Ariadne database uptodate with the
	latest changes.

    ******************************************************************/
	set_time_limit(0);

	require_once("../../ariadne.inc");
	require_once($ariadne."/bootstrap.php");
	require_once($store_config['code']."stores/".$store_config["dbms"]."store_install.phtml");

	require_once(AriadneBasePath . "/stores/axstore.phtml");
	require_once(AriadneBasePath . "/configs/axstore.phtml");


		// instantiate the store
	$inst_store = $store_config["dbms"]."store_install";
	$store=new $inst_store($root,$store_config);
	$store->rootoptions = $rootoptions;

	$args = array_merge($_GET, $_POST);

	$AR->user = current($store->call('system.get.phtml', '', $store->get('/system/users/admin/')));

	$old_version = current($store->call('system.get.value.phtml', '', $store->get('/system/ariadne/version/')));

	echo "Current Ariadne version: $old_version<br>\n";
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
		case '9.0-rc1.1':
				array_push($todo, array(
							"description" => "Installing cache store",
							"operation" => "9.0/install.cache_store.php",
							"newversion" => '9.0-rc1.2'
						));
		case '8.4-b2': // because of previous released upgrade scripts
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
							"newversion" => '9.0-rc1.6'
						));
		case '9.0-rc1.6':
				array_push($todo, array(
							"description" => "Update properties and path length",
							"operation" => "9.0/upgrade.database.php",
							"newversion" => '9.0-rc1'
						));
		case '9.0-rc1.7':
		case '9.0-rc1':
				array_push($todo, array(
							"description" => "Installing missing objects",
							"operation" => "9.0/install.missing.data.php",
							"newversion" => "9.0-rc2.1"
							));
		case '9.0-rc2.1':
				array_push($todo, array(
							"description" => "Bumping revision to 9.0",
							"operation" => "all/dummy.php",
							"newversion" => "9.0"
							));
	}


	if ($args["upgrade"] && count($todo)) {
		$task = array_shift($todo);
		echo "<div style=\"overflow: auto; width: 80%; min-height: 10%; max-height: 75%; border-color: black; border: 2px solid;\">";
			require($task["operation"]);
		echo "</div>";
		if (!$error) {
			echo "Ariadne database succesfully upgraded to: ".$task["newversion"]."<br>\n";
			$store->call('system.save.data.phtml', array('value' => $task["newversion"]), $store->get('/system/ariadne/version/'));
		} else {
			echo "Upgrade failed:<br>";
			echo "$error";
		}
	}

	if (count($todo)) {
		echo "The following will be done to get your Ariadne up to date:<br>\n";
		echo "<ul>\n";
		foreach ($todo as $task) {
			echo "<li>".$task["description"]." => ".$task["newversion"]."<br>";

		}
		echo "</ul>\n";
		echo "<p>Next: <a href=\"upgrade.php?upgrade=true\">".$todo[0]["description"]."</a></p>";
	}

	/* Finish execution */
	exit;
?>
