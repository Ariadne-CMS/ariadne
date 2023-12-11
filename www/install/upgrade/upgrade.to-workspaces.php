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

	$store_config["dbms"] = $store_config["dbms"] . "_workspaces";
	require_once($ariadne."/stores/".$store_config["dbms"]."store_install.phtml");


	// instantiate the store
	$inst_store = $store_config["dbms"]."store_install";
	$store=new $inst_store($root,$store_config);

	$session_config["dbms"] = $session_config["dbms"] . "_workspaces";
	$inst_store = $session_config["dbms"]."store_install";
	$sessionstore=new $inst_store(".",$session_config);

	$cache_config["dbms"] = $cache_config["dbms"] . "_workspaces";
	$inst_store = $cache_config["dbms"]."store_install";
	$cachestore=new $inst_store(".",$cache_config);

	echo "Trying to upgrade your database.\n";
	$store->upgrade();
	$sessionstore->upgrade();
	$cachestore->upgrade();

	/* Finish execution */
	exit;
?>
