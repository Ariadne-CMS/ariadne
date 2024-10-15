<?php
    /******************************************************************
     loader.php                                            Muze Ariadne
     ------------------------------------------------------------------
     Author: Muze (info@muze.nl)
     Date: 11 december 2002

     Copyright 2002 Muze

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

	Loader for the Ariadne Web Interface.

    ******************************************************************/

	require_once("./ariadne.inc");
	require_once($ariadne."/bootstrap.php");
	require_once( AriadneBasePath . "/modules/mod_itf/default.php" );

//	$ITF = new mod_ITF();
//	$ITF->record();


	if ($workspace = getenv("ARIADNE_WORKSPACE")) {
		include_once($store_config['code']."modules/mod_workspace.php");
		$layer = workspace::getLayer($workspace);
		if (!$layer) {
			$layer = 1;
		}

		if ($wspaths = getenv("ARIADNE_WORKSPACE_PATHS")) {
			$wspaths = explode(";", $wspaths);
			foreach ($wspaths as $wspath) {
				if ($wspath != '') {
					$store_config['layer'][$wspath] = $layer;
				}
			}
		} else {
			$store_config['layer'] = array('/' => $layer );
		}
	}



	ldCheckAllowedMethods($_SERVER['REQUEST_METHOD']);

	if (!isset($AR_PATH_INFO)) {
		$path_info = $_SERVER['PATH_INFO'];

		if ($path_info != '' ){
			// we have a full path request
			$AR_PATH_INFO = $path_info;
		} else {
			// we have a partial path request
			$scriptname  = $_SERVER['PHP_SELF'];
			$sitepath    = $_SERVER['ARSITEPATH'];
			$matches     = array();
			$pathMatchRe = "|(/-[^/]{4}-(?=/))?(/[a-z]{2}(?=/))?/(.*)|";
			if (preg_match($pathMatchRe,$scriptname,$matches)) {
				$session = $matches[1];
				$nls     = $matches[2];
				$path    = $matches[3];
			} else {
				$session = '';
				$nls     = '';
				$path    = substr($path,1);
			}

			$AR_PATH_INFO = $session . $nls . arc\path::collapse($sitepath) . $path;
		}
	}

	if (!$AR_PATH_INFO) {
		ldRedirect($_SERVER["PHP_SELF"]."/");
	} else {
		if (Headers_sent()) {
			error("The loader has detected that PHP has already sent the HTTP Headers. This error is usually caused by trailing white space or newlines in the configuration files. See the following error message for the exact file that is causing this:");
			Header("Misc: this is a test header");
		}
		@ob_end_clean(); // just in case the output buffering is set on in php.ini, disable it here, as Ariadne's cache system gets confused otherwise.

		ldProcessCacheControl();
		ldProcessRequest($AR_PATH_INFO);
	}
