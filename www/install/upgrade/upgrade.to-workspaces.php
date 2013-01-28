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
	require_once($ariadne."/configs/ariadne.phtml");
	require_once($ariadne."/configs/store.phtml");
	require_once($ariadne."/configs/axstore.phtml");

	$store_config["dbms"] = "mysql_workspaces";

	include_once($store_config['code']."stores/".$store_config["dbms"]."store_install.phtml");
	include_once($ax_config['code']."stores/".$ax_config["dbms"]."store.phtml");
	include_once($store_config['code']."modules/mod_session.phtml");
	include_once($store_config['code']."includes/loader.web.php");
	include_once($ariadne."/ar.php");

		// instantiate the store
	$inst_store = $store_config["dbms"]."store_install";
	$store=new $inst_store($root,$store_config);
	$store->rootoptions = $rootoptions;

	$args = array_merge($_GET, $_POST);

	echo "Trying to upgrade your database.\n";

	$store->upgrade();

	/* Finish execution */
	exit;
?>