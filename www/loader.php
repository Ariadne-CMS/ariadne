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
	require_once($ariadne."/configs/store.phtml");
	include_once($store_config['code']."stores/".$store_config["dbms"]."store.phtml");
	include_once($store_config['code']."modules/mod_session.phtml");
	include_once($store_config['code']."includes/loader.web.php");
	
	include_once($store_config['code']."modules/mod_virusscan.php");
	include_once($store_config['code']."modules/mod_stats.php");

	function fix_quotes(&$value) {
		if (is_array($value)) {
			reset($value);
			array_walk($value, 'fix_quotes');
		} else {
			$value=stripslashes($value);
		}
	}


	$PATH_INFO=$HTTP_SERVER_VARS["PATH_INFO"];
	if (!$PATH_INFO) {

		ldRedirect($HTTP_SERVER_VARS["PHP_SELF"]."/");
		exit;

	} else {

		// needed for IIS: it doesn't set the PHP_SELF variable.
		$PHP_SELF=$HTTP_SERVER_VARS["SCRIPT_NAME"].$PATH_INFO;
		$HTTP_SERVER_VARS["PHP_SELF"] = $PHP_SELF;
		if (Headers_sent()) {
			error("The loader has detected that PHP has already sent the HTTP Headers. This error is usually caused by trailing white space or newlines in the configuration files. See the following error message for the exact file that is causing this:");
			Header("Misc: this is a test header");
		}
		@ob_end_clean(); // just in case the output buffering is set on in php.ini, disable it here, as Ariadne's cache system gets confused otherwise. 

		// go check for a sessionid
		$root=$AR->root;
		$session_id=0;
		$re="^/-(.*)-/";
		if (eregi($re,$PATH_INFO,$matches)) {
			$session_id=$matches[1];
			$PATH_INFO=substr($PATH_INFO,strlen($matches[0])-1);
			$AR->hideSessionIDfromURL=false;
		} elseif ($AR->hideSessionIDfromURL) {
			global $HTTP_COOKIE_VARS;
			$ARCookie=stripslashes($HTTP_COOKIE_VARS["ARCookie"]);
			$cookie=@unserialize($ARCookie);
			if (is_array($cookie)) {
				$session_id=current(array_keys($cookie));
			}
		}

		// set the default user (public)
		$AR->login="public";

		// look for the template
		$split=strrpos($PATH_INFO, "/");
		$path=substr($PATH_INFO,0,$split+1);
		$function=substr($PATH_INFO,$split+1);
		if (!$function) {
			if (!$arDefaultFunction) {
				$arDefaultFunction="view.html";
			}
			$function=$arDefaultFunction;
			if ($arFunctionPrefix) {
				$function=$arFunctionPrefix.$function;
			}
			$PATH_INFO.=$function;
		}
		$ldCacheFilename=strtolower($PATH_INFO)."=";
		// yes, the extra '=' is needed, don't remove it. trust me.
		if (ldGetServerVar("QUERY_STRING")) {
			$ldCacheFilename.=ldGetServerVar("QUERY_STRING");
		}


		/*
			do not active output compression if:
				- it isnt enabled
				- the client doesn't explicitly states that it supports it
				- the gzcompress function isn't available
				- there is a session id in the request
		*/
		if (!$AR->output_compression 
				|| strpos($HTTP_SERVER_VARS["HTTP_ACCEPT_ENCODING"], "gzip")===false 
				|| !function_exists("gzcompress")
				|| $session_id
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
		
		$timecheck=time();
		if (file_exists($cachedimage) && 
			(strpos($HTTP_SERVER_VARS["ALL_HTTP"],"no-cache") === false) &&
			(strpos($HTTP_PRAGMA,"no-cache") === false) &&
			((($mtime=filemtime($cachedimage))>$timecheck) || ($mtime==0)) &&
			($HTTP_SERVER_VARS["REQUEST_METHOD"]!="POST")) {

			$ctime=filectime($cachedimage);
			if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $ctime) {
				// the mtime is used as expiration time, the ctime is the correct last modification time.
				// as an object clears the cache upon a save.
				ldHeader("HTTP/1.1 304 Not Modified");
			} else {
				// now send caching headers too, maximum 1 hour client cache.
				// FIXME: make this configurable. per directory? as a fraction?
				$freshness=$mtime-$timecheck;
				if ($freshness>3600) { 
					$cachetime=$timecheck+3600;
				} else {
					$cachetime=$mtime; 
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
				if ($session_id && !$AR->hideSessionIDfromURL) {
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
			}

		} else {
			/*
				start output buffering
			*/
			if ($AR->output_compression) {
				ob_start();
				ob_implicit_flush(0);
			}

			// look for the language
			$split=strpos(substr($PATH_INFO, 1), "/");
			$ARCurrent->nls=substr($path, 1, $split);
			if (!$AR->nls->list[$ARCurrent->nls]) {
				// not a valid language
				$ARCurrent->nls="";
				$nls=$AR->nls->default;
				$cachenls="";
				// but we can find out if the user has any preferences
				preg_match_all("%([a-zA-Z]{2}|\\*)[a-zA-Z-]*(?:;q=([0-9.]+))?%", $HTTP_SERVER_VARS["HTTP_ACCEPT_LANGUAGE"], $regs, PREG_SET_ORDER);
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
				ldSetNls($ARCurrent->nls);
				$nls=$ARCurrent->nls;
				$cachenls="/$nls";
			}

			// find (and fix) arguments
			set_magic_quotes_runtime(0);
			if (get_magic_quotes_gpc()) {
				// this fixes magic_quoted input
				fix_quotes($HTTP_GET_VARS);
				fix_quotes($HTTP_POST_VARS);
				$ARCookie=stripslashes($ARCookie);
			}
			$args=array_merge($HTTP_GET_VARS,$HTTP_POST_VARS);


			// instantiate the store
			$inst_store = $store_config["dbms"]."store";
			$store=new $inst_store($root,$store_config);
			$store->rootoptions = $rootoptions;

			if ($session_id) {
				//debugon("all");
				ldStartSession($session_id);

				if ($ARCurrent->session->get("ARSessionTimedout", 1)) {
					if (!$ARCurrent->session->get("oldArCallArgs", 1)) {
						$ARCurrent->session->put("oldArCallArgs", $args, 1);
						$ARCurrent->session->save(0, true);
					}
				} else {
					if ($ARCurrent->session->get("oldArCallArgs", 1)) {
						$args = $ARCurrent->session->get("oldArCallArgs", 1);
						$ARCurrent->session->put("oldArCallArgs", "", 1);
					}
				}
			}

			// load language file
			require($ariadne."/nls/".$nls);
			if (substr($function, -6)==".phtml") {
				// system template: no language check
				$ARCurrent->nolangcheck=1;
			}


			register_shutdown_function("ldOnFinish");

			$result = ldCheckLogin($args["ARLogin"], $args["ARPassword"]);
			if ($result!==true) {
				if ($result == LD_ERR_ACCESS) {
					$function = "user.login.html";
				} else if ($result == LD_ERR_SESSION) {
					$function = "user.session.timeout.html";
				}
			}
			
			// finally call the requested object
			unset($store->total);
			$store->call($function, $args, $store->get($path));
			if (!$store->total) {
				ldObjectNotFound($path, $function);
			}
		}

		// now check for outputbuffering (caching)
		if ($image=ob_get_contents()) {
			// first set clientside cache headers
			if (!$ARCurrent->arDontCache && !$nocache && ($cachetime=$ARCurrent->cachetime)) {
				if ($cachetime==-2) {
					$cachetime=999;
				}
				ldSetClientCache(true, time()+(($cachetime * 3600)/2));
			}


			if ($AR->output_compression) {
				/*
					FIXME: when output may not be compressed, it still get saved under 
					the cache/compressed/ directory so that another request will load
					it from cache. (maybe we can save it to cache/normal/ and
					create a (sym)link under cache/compressed/ to it? ).
				*/
				$ldCacheFilename = "/compressed".$ldCacheFilename;
				$skip_compression = false;

				if (is_array($ARCurrent->ldHeaders) && is_array($AR->output_compression_skip)) {
					while(!$skip_compression && (list($key, $ctype)=each($AR->output_compression_skip))) {
						if ($ARCurrent->ldHeaders["content-type: $ctype"]) {
							$skip_compression = true;
						}
					}
				}

				if (!$skip_compression) {
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
				}
				$image_len = strlen($image);
			} else {
				$image_len = strlen($image);
				if (!$AR->hideSessionIDfromURL && $ARCurrent->session && $ARCurrent->session->id) {
					$ldCacheFilename = "/session".$ldCacheFilename;
					$image = str_replace('-'.$ARCurrent->session->id.'-', '{arSessionID}', $image);
				} else {
					$ldCacheFilename = "/normal".$ldCacheFilename;
				}
			}

			// because we have the full content, we can now also calculate the content length
			ldHeader("Content-Length: ".$image_len);
			// flush the buffer, this will send the contents to the browser
			ob_end_flush();
			debug("loader: ob_end_flush()","all");
			// check whether caching went correctly, then save the cache
			if (is_array($ARCurrent->cache) && ($file=array_pop($ARCurrent->cache))) {
				error("cached() opened but not closed with savecache()");
			} else if (!$ARCurrent->arDontCache && !$nocache) {
				ldSetCache($ldCacheFilename, $ARCurrent->cachetime, $image, @implode("\n",$ARCurrent->ldHeaders));
			}
		}
	}

	/* Finish execution */
	exit;
?>