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
	require_once($ariadne."/configs/ariadne.phtml");
	require_once($ariadne."/configs/authentication.phtml");
	require_once($ariadne."/configs/store.phtml");

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

	include_once($store_config['code']."stores/".$store_config["dbms"]."store.phtml");
	include_once($store_config['code']."modules/mod_session.phtml");
	include_once($store_config['code']."includes/loader.web.php");
	include_once($store_config['code']."modules/mod_auth/".$auth_config['method'].".php");
	
	include_once($store_config['code']."modules/mod_virusscan.php");
	include_once($store_config['code']."modules/mod_stats.php");
	include_once($store_config['code']."modules/mod_ar.php");



	function fix_quotes(&$value) {
		if (is_array($value)) {
			reset($value);
			array_walk($value, 'fix_quotes');
		} else {
			$value=stripslashes($value);
		}
	}

	function ldGatherXSSInput(&$xss, $input) {
		if (is_array($input)) {
			foreach ($input as $value) {
				ldGatherXSSInput($xss, $value);
			}
		} else {
			$input = (string)$input;
			if (strlen($input) > 10) {
				if (preg_match('/[\'"<>]/', $input)) {
					$xss[strlen($input)][$input] = $input;
				}
			}
		}
	}

	function ldCheckAllowedTemplate($template) {
		// Check if a template is allowed to be called directly from the URL.


		if ($template == "system.list.folders.json.php") {
			// FIXME: this template is used to fetch folders in explore - it should be renamed to explore.list.folders.json.php;
			return true;
		} else if ($template == "system.list.objects.json.php") {
			// FIXME: this template is used to fetch objects in explore - it should be renamed to explore.list.objects.json.php;
			return true;
		} else if (preg_match('/^(system|ftp|webdav|soap)\./', $template)) {
			// Disallow all direct calls to system.*, ftp.*, webdav.*, soap.* templates;
			// FTP, webdav, soap should use their own loader instead.
			return false;
		}

		return true;
	}

	function ldProcessRequest($AR_PATH_INFO=null) {
		global $AR;
		global $ARCurrent;
		global $store_config;
		global $auth_config;
		global $store;
		global $context;

		// go check for a sessionid
		$root=$AR->root;
		$session_id=0;
		$re="^/-(.{4})-/";
		if (preg_match( '|'.$re.'|' , $AR_PATH_INFO , $matches )) {
			$session_id=$matches[1];
			$AR_PATH_INFO=substr($AR_PATH_INFO,strlen($matches[0])-1);
			$AR->hideSessionIDfromURL=false;
		} elseif ($AR->hideSessionIDfromURL) {
			$ARCookie=stripslashes($_COOKIE["ARCookie"]);
			$cookie=@unserialize($ARCookie);
			if (is_array($cookie)) {
				$session_id=current(array_keys($cookie));
			}
		}

		// set the default user (public)
		$AR->login="public";


		// look for the template
		$split=strrpos($AR_PATH_INFO, "/");
		$path=substr($AR_PATH_INFO,0,$split+1);
		$function=substr($AR_PATH_INFO,$split+1);
		if (!$function || !ldCheckAllowedTemplate($function) ) {
			if (!isset($arDefaultFunction) || $arDefaultFunction == '' ) {
				$arDefaultFunction="view.html";
			}
			$function=$arDefaultFunction;
			if (isset($arFunctionPrefix) && $arFunctionPrefix != '' ) {
				$function=$arFunctionPrefix.$function;
			}
			$AR_PATH_INFO.=$function;
		}

		// yes, the extra '=' is needed, don't remove it. trust me.
		$ldCacheFilename=strtolower($AR_PATH_INFO)."=";
		// for the new multiple domains per site option (per language), we need this
		// since the nls isn't literaly in the url anymore.
		$ldCacheFilename.=str_replace(':','=',str_replace('/','',$AR->host)).'=';

		$qs = ldGetServerVar("QUERY_STRING");
		if ($qs != '') {
			$ldCacheFilename.=sha1($qs);
		}

		/*
			do not active output compression if:
				- it isnt enabled
				- the client doesn't explicitly states that it supports it
				- the gzcompress function isn't available
				- there is a session id in the request
		*/
		if (!$AR->output_compression 
				|| strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip")===false 
				|| !function_exists("gzcompress")
				|| $session_id
				|| $AR->ESI
			) {

			$AR->output_compression = 0;
			if ($session_id && !$AR->hideSessionIDfromURL) {
				$cachedimage=$store_config["files"]."cache/session".$ldCacheFilename;
				$cachedheader=$store_config["files"]."cacheheaders/session".$ldCacheFilename;
			} else {
				$cachedimage=$store_config["files"]."cache/normal".$ldCacheFilename;
				$cachedheader=$store_config["files"]."cacheheaders/normal".$ldCacheFilename;
			}
		} else {
			$cachedimage=$store_config["files"]."cache/compressed".$ldCacheFilename;
			$cachedheader=$store_config["files"]."cacheheaders/compressed".$ldCacheFilename;
		}

		// mod_stats call
		$logstats = new stats();
		$logstats->log();

		if ($AR->ESI) {
			ob_start();
		}

		$timecheck=time();

		// FIXME: Chrome sometimes sends If-Modified-Since combined with Pragma: no-cache or Cache-control: no-cache;
		// The check should first check if a 304 not modified header can be sent;
		// then check if the cache-image can be used;
		// then pass it to Ariadne.
		// For now: pragma: no-cache check is removed, this makes Ariadne stubborn with returning cache-images.

		if (file_exists($cachedimage) &&
			((($mtime=filemtime($cachedimage))>$timecheck) || ($mtime==0)) &&
			($_SERVER["REQUEST_METHOD"]!="POST")) {
			ldHeader("X-Ariadne-Cache: Hit");

			$ctime=filemtime($cachedimage); // FIXME: Waarom moet dit mtime zijn? Zonder mtime werkt de if-modified-since niet;

			if (!$AR->ESI && $_SERVER['HTTP_IF_MODIFIED_SINCE'] && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $ctime) {
				// the mtime is used as expiration time, the ctime is the correct last modification time.
				// as an object clears the cache upon a save.

				// Send the original headers - they will already contain the correct max-age and expires values;
				if (file_exists($cachedheader)) {
					$filedata = file($cachedheader);
					if (is_array($filedata)) {
						while (list($key, $header)=each($filedata)) {
							ldHeader($header);
						}
					}
				}
				ldHeader("HTTP/1.1 304 Not Modified");
			} else {
				// now send caching headers too, maximum 1 hour client cache.
				// FIXME: make this configurable. per directory? as a fraction?
				$freshness=$mtime-$timecheck;
				if ($freshness>3600) { 
					$cachetime=$timecheck+3600;
				} else {
					$cachetime=$mtime; 
					// same '30 minutes' as used 13 lines above
					if($cachetime == 0){
						$cachetime = $timecheck + 1800;
					}
				}
				if (file_exists($cachedheader)) {
					$filedata = file($cachedheader);
					if (is_array($filedata)) {
						while (list($key, $header)=each($filedata)) {
							ldHeader($header);
						}
					}
				}
				ldSetClientCache(true, $cachetime, $ctime);
				ldHeader("X-Ariadne-Cache: Hit"); // Send this after the cached headers to overwrite the cached cache-miss header;

				if ($AR->ESI) {
					if (false && $_SERVER['HTTP_IF_MODIFIED_SINCE'] && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $ctime)) {
						ldHeader("HTTP/1.1 304 Not modified");
					} else {
						$data = file_get_contents($cachedimage);
						include_once($store_config['code']."modules/mod_esi.php");
						$data = ESI::esiProcess($data);
						if ($session_id && !$AR->hideSessionIDfromURL) {
							$tag = '{arSessionID}';
							$data = str_replace($tag, "-$session_id-", $data);
						}
						echo $data;
					}

				} else if ($session_id && !$AR->hideSessionIDfromURL) {
					$tag = '{arSessionID}';
					$tag_size = strlen($tag);
					$data = "";
					$fp = fopen($cachedimage, "r");
					while (!feof($fp)) {
						$data .= fread($fp, 4096);
						$data = str_replace($tag, "-$session_id-", $data);
						echo substr($data, 0, 4096-$tag_size);
						$data = substr($data, 4096-$tag_size);
					}
					echo $data;
					fclose($fp);
				} else {
					readfile($cachedimage);
				}
				$nocache = true; // Prevent recaching cached image;
			}

		} else {
			/*
				start output buffering
			*/
			ob_start();
			global $ldOutputBufferActive;
			$ldOutputBufferActive = true;
			ob_implicit_flush(0);

			// look for the language
			$split=strpos(substr($AR_PATH_INFO, 1), "/");
			$ARCurrent->nls=substr($path, 1, $split);
			if (!isset($AR->nls->list[$ARCurrent->nls]) ) {
				// not a valid language
				$ARCurrent->nls="";
				$nls=$AR->nls->default;
				$cachenls="";
				// but we can find out if the user has any preferences
				preg_match_all("%([a-zA-Z]{2}|\\*)[a-zA-Z-]*(?:;q=([0-9.]+))?%", $_SERVER["HTTP_ACCEPT_LANGUAGE"], $regs, PREG_SET_ORDER);
				$ARCurrent->acceptlang=array();
				$otherlangs=array();
				$otherq=false;
				foreach ($regs as $reg) {
					if (!isset($reg[2])) {
						$reg[2]=1;
					}
					if ($reg[1]=="*") {
						$otherq=$reg[2];
					} else if ($AR->nls->list[$reg[1]]) {
						$otherlangs[]=$reg[1];
						$ARCurrent->acceptlang[$reg[1]]=$reg[2];
					}
				}
				if ($otherq !== false) {
					$otherlangs=array_diff(array_keys($AR->nls->list), $otherlangs);
					foreach ($otherlangs as $lang) {
						$ARCurrent->acceptlang[$lang]=$otherq;
					}
				}
				arsort($ARCurrent->acceptlang);
			} else {
				// valid language
				$path=substr($path, $split+1);
				// ldSetNls($ARCurrent->nls);
				$nls=$ARCurrent->nls;
				$cachenls="/$nls";
			}

			// find (and fix) arguments
			ini_set('magic_quotes_runtime', 0); 
			if (get_magic_quotes_gpc()) {
				// this fixes magic_quoted input
				fix_quotes($_GET);
				fix_quotes($_POST);
				$ARCookie=stripslashes($ARCookie);
			}
			$args=array_merge($_GET,$_POST);


			// instantiate the store
			$inst_store = $store_config["dbms"]."store";
			$store=new $inst_store($root,$store_config);
			//$store->rootoptions = $rootoptions;

			if ($session_id) {
				ldStartSession($session_id);
			}

			// instantiate the ARnls
			global $ARnls;
			$ARnls = ar('nls')->dict($AR->nls->default);
			if( $ARCurrent->nls != "" ) {
				ldSetNls($nls);
			}



			if (substr($function, -6)==".phtml") {
				// system template: no language check
				$ARCurrent->nolangcheck=1;
			}


			register_shutdown_function("ldOnFinish");

			$auth_class = "mod_auth_".$auth_config["method"];
			$mod_auth = new $auth_class($auth_config);
			$username = ( isset($args["ARLogin"]) ? $args["ARLogin"] : null );
			$password = ( isset($args["ARPassword"]) ? $args["ARPassword"] : null );
			$result = $mod_auth->checkLogin($username, $password, $path);
			if ($result!==true) {
				if ($result == LD_ERR_ACCESS) {
					ldAccessDenied($path, $ARnls["accessdenied"], $args, $function);
					$function = false;
				} else if ($result == LD_ERR_SESSION) {
					ldAccessTimeout($path, $ARnls["sessiontimeout"], $args, $function);
					$function = false;
				} else if ($result == LD_ERR_EXPIRED) {
					ldAccessPasswordExpired($path, $ARnls["sessionpasswordexpired"], $args, $function);
					$function = false;
				}
			}
			
			// valid new login, without a session, morph to login.redirect.php to redirect to a session containing url
			if( !$session_id && $args["ARLogin"] && $args["ARPassword"] && $function !== false && !$AR->hideSessionIDfromURL ) {
				if (!$ARCurrent->session->get("oldArCallArgs", 1)) {
					$ARCurrent->session->put("oldGET", $_GET, 1);
					$ARCurrent->session->put("oldPOST", $_POST, 1);
					$ARCurrent->session->put("oldArCallArgs", $args, 1);
					$ARCurrent->session->save(0, true);
				}
				if ($arDefaultFunction !== $function) {
					$args["arRequestedTemplate"] = $function;
				} else {
					$args["arRequestedTemplate"] = "";
				}
				$function = "login.redirect.php";
			} else if( $session_id ) {
				if ($ARCurrent->session->get("ARSessionTimedout", 1)) {
					if (!$ARCurrent->session->get("oldArCallArgs", 1)) {
						$ARCurrent->session->put("oldGET", $_GET, 1);
						$ARCurrent->session->put("oldPOST", $_POST, 1);
						$ARCurrent->session->put("oldArCallArgs", $args, 1);
						$ARCurrent->session->save(0, true);
					}
				} else {
					if ($ARCurrent->session->get("oldArCallArgs", 1)) {
						$_GET = array_merge( $_GET, (array)$ARCurrent->session->get("oldGET", 1) );
						$_POST = array_merge( $_POST, (array)$ARCurrent->session->get("oldPOST", 1) );
						$args = $ARCurrent->session->get("oldArCallArgs", 1);
						$args = array_merge( $_GET, $_POST, $args); // $args, $_GET, $_POST );
						$ARCurrent->session->put("oldArCallArgs", "", 1);
						$ARCurrent->session->put("oldGET", "", 1);
						$ARCurrent->session->put("oldPOST", "", 1);
					}
				}
			}

			$xss_vars = array();
			ldGatherXSSInput($xss_vars, $_GET);
			ldGatherXSSInput($xss_vars, $_POST);

			ldGatherXSSInput( $xss_vars, $function );
			ldGatherXSSInput( $xss_vars, $path );
			global $ldXSSProtectionActive;
			if (count($xss_vars)) {
				$ldXSSProtectionActive = true;
			}

			if ($function!==false) {
				// finally call the requested object
				unset($store->total);
				$store->call($function, $args, $store->get($path));
				if (!$store->total) {
					ldObjectNotFound($path, $function, $args);
				}
			}

			if (count($xss_vars)) {
				$image = ob_get_contents();
				ob_clean();

				$header = $ARCurrent->ldHeaders["content-type"];
				$xssDetected = false;
				preg_match('/^content-type:\s+([^ ;]+)/i', $header, $matches);
				$mimetype = strtolower($matches[1]);
				if (substr($mimetype, 0, 5) == 'text/') {
					krsort($xss_vars, SORT_NUMERIC);
					foreach ($xss_vars as $values) {
						if (is_array($values)) {
							foreach ($values as $value) {
								$occurances = substr_count($image, $value);
								if ($occurances > 0 ) {
									$xssDetected = true;
									break 2;
								}
							}
						}
					}
				}

				if ($xssDetected) {
					$newargs["arRequestedArgs"]     = $args;
					$newargs["arRequestedTemplate"] = $function;
					$newargs["arSuspectedArgs"]     = $xss_vars;
					$newargs["arResultOutput"]      = $image;
					$store->call('user.xss.html', $newargs, $store->get($path));
				} else {
					echo $image;
				}
			}
		}

		// now check for outputbuffering (caching)
		if ($image=ob_get_contents()) {
			if ($_SERVER['REQUEST_METHOD']!='GET' || ($DB["wasUsed"] > 0)) {
				$nocache = true;
			}

			// first set clientside cache headers

			if (!$ARCurrent->arDontCache && !$nocache && ($cachetime=$ARCurrent->cachetime)) {
				if ($cachetime==-2) {
					$cachetime = 999;
					$ARCurrent->cachetime = 999;
					ldSetClientCache(true, time()+1800); // Let the client revalidate cached image after 30 minutes;
				} else {
					// FIXME: Is it wise to give the client the same expiry as the cachetime? Or should this also be 30 minutes?
					ldSetClientCache(true, time()+1800);
					// $ARCurrent->cachetime = 999;
				}
			}


			$image_len = strlen($image);

			if ($AR->ESI) {
				$AR->output_compression = false;
			}

			if (!$AR->hideSessionIDfromURL && $ARCurrent->session && $ARCurrent->session->id) {
				$ldCacheFilename = "/session".$ldCacheFilename;
				$image = str_replace('-'.$ARCurrent->session->id.'-', '{arSessionID}', $image);
			} else {
				if ($AR->output_compression) {
					$skip_compression = true;

					// prevent errors if the config file is missing this option
					if(!is_array($AR->output_compression_type)){
						$AR->output_compression_type = array();
					}

					$contenttype="content-type";
					$contenttypelength=strlen($contenttype);
					$headers = $ARCurrent->ldHeaders;
					
					if($ARCurrent->ldHeaders["content-type"]) {
						$header = $ARCurrent->ldHeaders["content-type"];
						preg_match('/^content-type:\s+([^ ;]+)/i',$header,$matches);
						$mimetype = $matches[1];
						if(isset($mimetype)){
							// dublecheck mimetype agains whitelist
							foreach($AR->output_compression_type as $compress_match){
								if(preg_match($compress_match,$mimetype)){
									$skip_compression = false;
									break;
								}
							}
						}
					}

					if (!$skip_compression) {
						$ldCacheFilename = "/compressed".$ldCacheFilename;
						ob_end_clean();
						ob_start();
						$crc = crc32($image);
						$size = strlen($image);
						$image = gzcompress($image, $AR->output_compression);
						$image = substr($image, 0, strlen($image) - 4);
						ldHeader("Content-Encoding: gzip");
						/* add header */
						$image = "\x1f\x8b\x08\x00\x00\x00\x00\x00".$image;
						$image.= pack('V', $crc).pack('V', $size);
						echo $image;
					} else {
						$ldCacheFilename = "/normal".$ldCacheFilename;
					}
					$image_len = strlen($image);
				} else {
					$ldCacheFilename = "/normal".$ldCacheFilename;
				}
			}
			// because we have the full content, we can now also calculate the content length
			ldHeader("Content-Length: ".$image_len);
			ldHeader("X-Ariadne-Cache: Miss");

			// flush the buffer, this will send the contents to the browser
			ob_end_flush();
			debug("loader: ob_end_flush()","all");

			// check whether caching went correctly, then save the cache
			if (is_array($ARCurrent->cache) && ($file=array_pop($ARCurrent->cache))) {
				error("cached() opened but not closed with savecache()");
			} else if (!$ARCurrent->arDontCache && !$nocache) {
				if ($store) {
					ldSetCache($ldCacheFilename, $ARCurrent->cachetime, $image, @implode("\n",$ARCurrent->ldHeaders));
				}
			}
		}

		if ($AR->ESI) {
			$image = ob_get_contents();
			ob_end_clean();
			include_once($store_config['code']."modules/mod_esi.php");
			$image = ESI::esiProcess($image);

			if ($ARCurrent->arDontCache) {
				// FIXME: ook de cachetime 'niet cachen' uit het cachedialoog werkend maken...  || $ARCurrent->cachetime == 0) {
				ldSetClientCache(false);
			}
			echo $image;
		}
	}

	if(!isset($AR_PATH_INFO)){
		$AR_PATH_INFO=$_SERVER["PATH_INFO"];
	}

	if (!$AR_PATH_INFO) {
		ldRedirect($_SERVER["PHP_SELF"]."/");
		exit;
	} else {
		// needed for IIS: it doesn't set the PHP_SELF variable.
		if(!isset( $_SERVER["PHP_SELF"])){
			$_SERVER['PHP_SELF']=$_SERVER["SCRIPT_NAME"].$AR_PATH_INFO;
		}
		if (Headers_sent()) {
			error("The loader has detected that PHP has already sent the HTTP Headers. This error is usually caused by trailing white space or newlines in the configuration files. See the following error message for the exact file that is causing this:");
			Header("Misc: this is a test header");
		}
		@ob_end_clean(); // just in case the output buffering is set on in php.ini, disable it here, as Ariadne's cache system gets confused otherwise.

		ldProcessRequest($AR_PATH_INFO);
	}
	/* Finish execution */
	exit;
?>