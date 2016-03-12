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

	// set update in progress for faster checks
	$AR->upgradeInProgress = true;

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
	require_once( 'tasks.php' );

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
