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

	function ldCacheRequest($AR_PATH_INFO=null) {
		ob_start();

		global $ARCurrent;
		$ARCurrent->refreshCacheOnShutdown = true;
		ldProcessRequest($AR_PATH_INFO);
		ob_end_clean();
	}

	function ldProcessRequest($AR_PATH_INFO=null) {
		global $AR;
		global $ARCurrent;
		global $store_config;
		global $auth_config;
		global $cache_config;
		global $store;
		global $context;
		global $DB;
		global $path;
		global $function;
		global $nls;

		$writecache = false;

		// go check for a sessionid
		$root=$AR->root;
		$session_id=0;
		$re="^/-(.{4})-/";

		$originalPathInfo = $AR_PATH_INFO; // Store this to pass to the refresh cache on shutdown function;

		if (preg_match( '|'.$re.'|' , $AR_PATH_INFO , $matches )) {
			$session_id=$matches[1];
			$AR_PATH_INFO=substr($AR_PATH_INFO,strlen($matches[0])-1);
			$AR->hideSessionIDfromURL=false;
		} elseif ($AR->hideSessionIDfromURL) {
			$cookies = (array) ldGetCredentials();
			$current = ldGetCookieSession();
			if ( array_key_exists( $current, $cookies ) ) {
				$session_id = $current;
			}
		}

		// set the default user (public)
		$AR->login="public";


		// look for the template
		$split=strrpos($AR_PATH_INFO, "/");
		$path=substr($AR_PATH_INFO,0,$split+1);
		$function=substr($AR_PATH_INFO,$split+1);
		if (!$function ) {
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

		if ( $session_id ) {
			$cachedimage=$store_config["files"]."cache/session".$ldCacheFilename;
			$cachedheader=$store_config["files"]."cacheheaders/session".$ldCacheFilename;
		} else {
			$cachedimage=$store_config["files"]."cache/normal".$ldCacheFilename;
			$cachedheader=$store_config["files"]."cacheheaders/normal".$ldCacheFilename;
		}

		if ($AR->ESI) {
			ob_start();
		}

		$timecheck=time();

		if (file_exists($cachedimage)) {
			$staleTotalTime = filemtime($cachedimage) - filectime($cachedimage);
			$staleCurrent = $timecheck - filectime($cachedimage);
			if( $staleTotalTime != 0) {
				$stalePercentage = sprintf("%.2f", 100 * $staleCurrent / $staleTotalTime);
			} else {
				$stalePercentage = 100;
			}
			if ($stalePercentage < 0) {
				$stalePercentage = 0;
			} else if ($stalePercentage > 100) {
				$stalePercentage = 100;
			}
			if (!headers_sent()) {
				header("X-Ariadne-Cache-Stale: $stalePercentage%");
			}
		}

		// add min-fresh if the client asked for it
		if (isset($ARCurrent->RequestCacheControl["min-fresh"])) {
			$timecheck += $ARCurrent->RequestCacheControl["min-fresh"];
		}

		if (
			file_exists($cachedimage) &&
			((($mtime=@filemtime($cachedimage)) > $timecheck) || ($mtime==0)) &&
			($_SERVER["REQUEST_METHOD"]!="POST") &&
			($ARCurrent->RequestCacheControl["no-cache"] != true ) &&
			($ARCurrent->refreshCacheOnShutdown !== true)
		) {
			$ctime=filemtime($cachedimage); // FIXME: Waarom moet dit mtime zijn? Zonder mtime werkt de if-modified-since niet;

			if (rand(20,80) < $stalePercentage) {
				header("X-Ariadne-Cache-Refresh: refreshing on shutdown");
				register_shutdown_function("ldCacheRequest", $originalPathInfo); // Rerun the request with the original path info;
			} else {
				header("X-Ariadne-Cache-Refresh: skipped, still fresh enough");
			}

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
				header("X-Ariadne-Cache: Hit");
				ldHeader("HTTP/1.1 304 Not Modified");
			} else {
				header("X-Ariadne-Cache: Hit");
				if (file_exists($cachedheader)) {
					// Cache header file also contains information about Cache-control;
					$filedata = file($cachedheader);
					if (is_array($filedata)) {
						while (list($key, $header)=each($filedata)) {
							ldHeader($header);
						}
					}
				}
				header("X-Ariadne-Cache: Hit"); // Send this after the cached headers to overwrite the cached cache-miss header;

				if ($AR->ESI) {
					if (false && $_SERVER['HTTP_IF_MODIFIED_SINCE'] && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $ctime)) {
						ldHeader("HTTP/1.1 304 Not modified");
					} else {
						$data = file_get_contents($cachedimage);
						include_once($store_config['code']."modules/mod_esi.php");

						// Replace the session IDs before the ESI process call to pass the correct session ID information...
						if ($session_id && !$AR->hideSessionIDfromURL) {
							$tag = '{arSessionID}';
							$data = str_replace($tag, "-$session_id-", $data);
						}

						$data = ESI::esiProcess($data);

						// ... and then replace the session IDs that were generated in de ESI case;
						$tag = '{arSessionID}';
						if ($session_id && !$AR->hideSessionIDfromURL) {
							$data = str_replace($tag, "-$session_id-", $data);
						} else if ($session_id && $AR->hideSessionIDfromURL ) {
							$data = str_replace($tag, '', $data);
						}
						echo $data;
					}

				} else if ($session_id) {
					$tag = '{arSessionID}';
					$data = file_get_contents($cachedimage);
					$tag = '{arSessionID}';
					if (!$AR->hideSessionIDfromURL) {
						$data = str_replace($tag, "-$session_id-", $data);
					} else {
						$data = str_replace($tag, '', $data);
					}
					echo $data;
				} else {
					readfile($cachedimage);
				}
			}
			$writecache = false; // Prevent recaching cached image;
		} else {
			if (!headers_sent()) {
				header("X-Ariadne-Cache: Miss");
			}

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
			if( $ARCurrent->nls != "" ) {
				ldSetNls($nls);
			}


			if (substr($function, -6)==".phtml") {
				// system template: no language check
				$ARCurrent->nolangcheck=1;
			}
			$ext = pathinfo($function, PATHINFO_EXTENSION);
			switch ( $ext ) {
				case 'css':
					ldSetContent('text/css; charset=utf-8');
				break;
				case 'js':
					ldSetContent('application/javascript; charset=utf-8');
				break;
				case 'json':
					ldSetContent('application/json; charset=utf-8');
				break;
				case 'xml':
					ldSetContent('text/xml; charset=utf-8');
				break;
				case 'jpg':
					ldSetContent('image/jpeg');
				break;
				case 'gif':
					ldSetContent('image/gif');
				break;
				case 'png':
					ldSetContent('image/png');
				break;
				case 'svg':
					ldSetContent('image/svg+xml');
				break;
				default:
					ldSetContent('text/html; charset=utf-8');
				break;
			}
			$ARCurrent->arContentTypeSent = true;

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
				} else if ($result == LD_ERR_SESSION && !$AR->hideSessionIDfromURL ) {
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
				if (ldCheckAllowedTemplate($function) ) {
					$store->call($function, $args, $store->get($path));
					$writecache = true;
				}
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
					$newargs = array();
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
			// Calculate browser side cache settings based on settings collected in the call chain;
			//
			// Rules: do not cache wins. short cache time wins over longer cache time. Unset values don't get to play.
			//
			// Overlord rule: if the request method was not a get, or debugging was used, do not cache. Ever.
			//
			// If pinp says arDontCache, then do not cache;
			//
			// If ESI was used and hit a cached image, use the cache settings from the cache image;
			if ($_SERVER['REQUEST_METHOD']!='GET' || ($DB["wasUsed"] > 0)) {
				// Do not cache on client.
				ldSetBrowserCache(false);
			} else if (is_array($ARCurrent->cache) && ($file=array_pop($ARCurrent->cache))) {
				// This will generate an error, do not cache on client;
				ldSetBrowserCache(false);
			} else if ($ARCurrent->arDontCache) {
				// PINP told us not to cache;
				ldSetBrowserCache(false);
			} else if (!$writecache) {
				// Image came from the cache, it already has browser cache headers;
			} else {
				// Defaults for browser caching;
				// Calls without session: public, max-age 1800;
				// Calls with session without call chain (disk templates): private, no-cache no-store must-revalidate max-age=0
				// Calls with session with call chain (pinp templates): private, max-age=1800;
				// FIXME: Make the calls with session less trigger happy on not caching;

				/* if ($session_id && sizeof($ARCurrent->cacheCallChainSettings)) {
					// With session and pinp templates;
					$browserCachePrivate = true;
					$browserCacheMaxAge = 1800;
					$browserCacheNoStore = false;
					$browserCacheNoCache = false;
					$browserCacheMustRevalidate = false;
				} else */
				if ($session_id) {
					// With session, disk templates only
					$browserCachePrivate = true;
					$browserCacheMaxAge = 0;
					$browserCacheNoStore = true;
					$browserCacheNoCache = true;
					$browserCacheMustRevalidate = true;
				} else {
					// Without session and all other corner cases;
					$browserCachePrivate = false;
					$browserCacheMaxAge = 1800;
					$browserCacheNoStore = false;
					$browserCacheNoCache = false;
					$browserCacheMustRevalidate = false;
				}

				$browserCachecacheSetting = 0; // Default = inherit;

				// FIXME: The defaults for with session ID are now to not cache;
				if(is_array($ARCurrent->cacheCallChainSettings) ) {
					foreach ($ARCurrent->cacheCallChainSettings as $objectId => $pathCacheSetting) {
						$browserCachePrivate = $browserCachePrivate || $pathCacheSetting['browserCachePrivate']; // If anyone says 'private', make it so.
						$browserCacheNoStore = $browserCacheNoStore || $pathCacheSetting['browserCacheNoStore']; // If anyone says 'no-store', make it so.
						$browserCacheNoCache = $browserCacheNoCache || $pathCacheSetting['browserCacheNoCache']; // If anyone says 'no-cache', make it so.
						$browserCacheMustRevalidate = $browserCacheMustRevalidate || $pathCacheSetting['browserCacheMustRevalidate']; // If anyone says 'must-revalidate', make it so.
						$browserCacheNoTransform = $browserCacheNoTransform || $pathCacheSetting['browserCacheNoTransform']; // If anyone says 'no-transform', make it so.
						$browserCacheProxyRevalidate = $browserCacheProxyRevalidate || $pathCacheSetting['browserCacheProxyRevalidate']; // If anyone says 'proxy-revalidate', make it so.

						$browserCacheMaxAge = isset($pathCacheSetting['browserCacheMaxAge']) ? min($browserCacheMaxAge, $pathCacheSetting['browserCacheMaxAge']) : $browserCacheMaxAge;
						$browserCacheSMaxAge = isset($pathCacheSetting['browserCacheSMaxAge']) ? min($browserCacheSMaxAge, $pathCacheSetting['browserCacheSMaxAge']) : $browserCacheSMaxAge;
					}
				}

				ldSetBrowserCache(
					array(
						"browserCachePrivate" => $browserCachePrivate,
						"browserCacheNoStore" => $browserCacheNoStore,
						"browserCacheNoCache" => $browserCacheNoCache,
						"browserCacheMustRevalidate" => $browserCacheMustRevalidate,
						"browserCacheNoTransform" => $browserCacheNoTransform,
						"browserCacheProxyRevalidate" => $browserCacheProxyRevalidate,
						"browserCacheMaxAge" => $browserCacheMaxAge,
						"browserCacheSMaxAge" => $browserCacheSMaxAge
					)
				);
			}


			$image_len = strlen($image);

			if (!$AR->hideSessionIDfromURL && $ARCurrent->session && $ARCurrent->session->id) {
				$ldCacheFilename = "/session".$ldCacheFilename;
				$image = str_replace('-'.$ARCurrent->session->id.'-', '{arSessionID}', $image);
			} else {
				$ldCacheFilename = "/normal".$ldCacheFilename;
			}
			// because we have the full content, we can now also calculate the content length
			ldHeader("Content-Length: ".$image_len);


			// flush the buffer, this will send the contents to the browser
			ob_end_flush();
			debug("loader: ob_end_flush()","all");


			// Calculate server side cache settings based on settings collected in the call chain;
			//
			// Rules: do not cache wins. short cache time wins over longer cache time. Unset values don't get to play.
			//
			// Overlord rule: if the request method was not a get, or debugging was used, do not cache. Ever.
			//
			// If pinp says arDontCache, then do not cache;
			//
			// If ESI was used and hit a cached image, do not write the image;

			if ($_SERVER['REQUEST_METHOD']!='GET' || ($DB["wasUsed"] > 0)) {
				// Do not cache on server.
				// header("X-Ariadne-Cache-Skipped: DB Used");
			} else if (is_array($ARCurrent->cache) && ($file=array_pop($ARCurrent->cache))) {
				error("cached() opened but not closed with savecache()");
				// header("X-Ariadne-Cache-Skipped: cached problem.");
			} else if ($ARCurrent->arDontCache) {
				// PINP told us not to cache;
				// header("X-Ariadne-Cache-Skipped: arDontCache");
			} else if (!$writecache) {
				// ESI was used and hit a cached image, do not write the image;
				// header("X-Ariadne-Cache-Skipped: cached image used");
			} else {
				// header("X-Ariadne-Cache-Skipped: Writing cache now");
				// Cache setting values:
				// -2 = Refresh on change; Set the cache time on server to 999 hours (unlimited);
				// -1 = Do not cache
				// 0  = Inherit
				// > 0: Refresh on request. The number is the amount of hours that the cache is 'fresh'. This can be a fraction/float value;

				$cacheSetting = 0; // Default = inherit;
				if( is_array($ARCurrent->cacheCallChainSettings)) {
					foreach ($ARCurrent->cacheCallChainSettings as $objectId => $pathCacheSetting) {
						$serverCache = $pathCacheSetting['serverCache'];

						if ($serverCache == -2) {
							// Sorry, we meant that the cache image should be valid forever;
							$serverCache = 999;
						}

						if ($serverCache == 0 || !isset($serverCache)) {
							// This path does not want to play;
							continue;
						}
						if ($cacheSetting == 0) {
							$cacheSetting = $serverCache;
						} else {
							$cacheSetting = min($serverCache, $cacheSetting);
						}

						if ($cacheSetting == -1) {
							// If someone told us to not cache, skip checking because nothing anyone else tells us will change this fact.
							break;
						}
					}
				}
				// header("X-Ariadne-Cache-Setting: $cacheSetting");

				if ($cacheSetting > 0) {
					// If we are allowed to cache, write the image now.
					if ($store) { // Sanity check to only write cache images if a store was initialized;
						// FIXME: cacheCallChainSettings contains the objects that were called for this cache image;
						// FIXME: cacheTemplateChain containers the templates that were called for this cache image;

						ldSetCache($ldCacheFilename, $cacheSetting, $image, @implode("\n",$ARCurrent->ldHeaders));
						$cachestore=new cache($cache_config);
						$cachestore->save($ldCacheFilename, $ARCurrent->cacheCallChainSettings, $ARCurrent->cacheTemplateChain);
					}
				}
			}

		}

		if ($AR->ESI > 0) {
			// Prevent ESI from looping when the ESI result has ESI tags in them.
			// Reducing the AR->ESI number by 1 gives the flexibility to allow 2 or 3 ESI loops if desired.
			// Setting it to false would mean you only get 1 ESI loop, which might not be the desired effect.
			$AR->ESI = (int) $AR->ESI;
			$AR->ESI--;

			$image = ob_get_contents();
			ob_end_clean();
			include_once($store_config['code']."modules/mod_esi.php");
			$image = ESI::esiProcess($image);
			$image_len = strlen($image);

			if ($ARCurrent->arDontCache) {
				// FIXME: ook de cachetime 'niet cachen' uit het cachedialoog werkend maken...  || $ARCurrent->cachetime == 0) {
				ldSetBrowserCache(false);
			}
			ldHeader("Content-Length: ".$image_len);
			echo $image;
		}
	}

	ldCheckAllowedMethods($_SERVER['REQUEST_METHOD']);

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

		ldProcessCacheControl();
		ldProcessRequest($AR_PATH_INFO);
	}
	/* Finish execution */
	exit;
