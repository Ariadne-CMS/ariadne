<?php
	require_once("../www/ariadne.inc");
	require_once($ariadne."/configs/ariadne.phtml");
	require_once($ariadne."/configs/store.phtml");
	include_once($store_config['code']."stores/".$store_config["dbms"]."store.phtml");
	include_once($store_config['code']."modules/mod_session.phtml");
	include_once($store_config['code']."modules/mod_soap.phtml");
	include_once($store_config['code']."includes/loader.soap.php");

	function fix_quotes(&$value) {
		if (is_array($value)) {
			reset($value);
			array_walk($value, 'fix_quotes');
		} else {
			$value=stripslashes($value);
		}
	}

	function unpack_array_names($source, &$target) {
		if (is_array($source)) {
			reset($source);
			while (list($key, $val) = each($source)) {
				$kpos = strpos($key, '.');
				if ($kpos !== false) {
					$count = 0;
					$targetkey = substr($key, 0, $kpos);
					if (!is_array($target[$targetkey])) {
						$target[$targetkey] = Array();
					}
					$subtarget = &$target[$targetkey];
					debug("creating array ($targetkey)");
					do {
						$count++;
						if ($count > 10) {
							debug("endless loop detected, dying");
							exit;
						}
						$kendpos = strpos($key, '.', $kpos+1);
						if (!$kendpos) {
							$kendpos = strlen($key)-1;
						}
						debug("key kpos($kpos) kendpos($kendpos)");
						$klen = $kendpos - $kpos;
						$targetkey = substr($key, $kpos+1, $klen);
						debug("soap::unpack_array_names found($targetkey)");

						if (!is_array($subtarget[$targetkey])) {
							$subtarget[$targetkey] = Array();
						}
						$subtarget = &$subtarget[$targetkey];

						$kpos = strpos($key, '.', $kendpos+1);
					} while ($kpos !== false);
					debug("soap::unpack_array_names   setting value\n");
					$subtarget = $val;
				} else {
					unpack_array_names($val, $target);
				}
			}
		}

	}


	$PATH_INFO=$HTTP_SERVER_VARS["PATH_INFO"];
	if (!$PATH_INFO) {

		ldRedirect($HTTP_SERVER_VARS["PHP_SELF"]."/");
		exit;

	} else {

		@ob_end_clean(); // just in case the output buffering is set on in php.ini, disable it here, as Ariadne's cache system gets confused otherwise. 

		// go check for a sessionid
		$root=$AR->root;

		// set the default user (public)
		$AR->login="public";

		// look for the template
		$split=strrpos($PATH_INFO, "/");
		$path=substr($PATH_INFO,0,$split+1);


		/* remove template from PATH_INFO */
		if (substr($PATH_INFO,$split+1)) {
			$PATH_INFO=substr($PATH_INFO, 0, $split);
		}

		// look for the language
		$split=strpos(substr($PATH_INFO, 1), "/");
		$ARCurrent->nls=substr($path, 1, $split);
		if (!$AR->nls->list[$ARCurrent->nls]) {
			// not a valid language
			$ARCurrent->nls="";
			$nls=$AR->nls->default;
			$cachenls="";
		} else {
			// valid language
			$path=substr($path, $split+1);
			ldSetNls($ARCurrent->nls);
			$nls=$ARCurrent->nls;
			$cachenls="/$nls";
		}

		$soapserver = new soap_server;
		debug($HTTP_RAW_POST_DATA);
		$arguments  = $soapserver->get_request($HTTP_RAW_POST_DATA);
		$function   = "soap.".strtolower($soapserver->methodname).".phtml";


		ob_start();
			echo "Arguments: \n";
			print_r($arguments);
			debug(ob_get_contents());
		ob_end_clean();
				
		if ($arguments["arUnpackArrayNames"]) {
			debug("loader starting unpackarraynames\n\n");
			unpack_array_names($arguments, $arguments);
		}

		debug("soap::request ($path) ('$function')", "loader");

		// instantiate the store
		$inst_store = $store_config["dbms"]."store";
		$store=new $inst_store($root,$store_config);
		$store->rootoptions = $rootoptions;

		// load language file
		require($ariadne."/nls/".$nls);
		$ARCurrent->nolangcheck=1;

		if (!ldCheckLogin($arguments["ARLogin"], $arguments["ARPassword"])) {
			ldCheckLogin("public", "none");
		}			

		// finally call the requested object
		$result = current($store->call($function, $arguments, $store->get($path)));
		if (!$store->total) {
			ldObjectNotFound($path, $soapserver->methodname);
		} else {
			if (!$SOAP_Fault) {
				debug("soap::returnvalue ($result)", "loader");
				$soapserver->send_returnvalue($result);
			}
		}
		if ($SOAP_Fault) {
			debug("soap::fault (".$SOAP_Fault.")", "loader");
			$soapserver->send_returnvalue($SOAP_Fault);
		}
		$store->close();
	}

	// save session data
	if ($ARCurrent->session) {
		$ARCurrent->session->save();
	}

?>