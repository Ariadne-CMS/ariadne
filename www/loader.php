<?php
	require_once("./ariadne.inc");
	require_once($ariadne."/configs/ariadne.phtml");
	require_once($ariadne."/configs/store.phtml");
	include_once($ariadne."/stores/".$store_config["dbms"]."store.phtml");
	include_once($ariadne."/modules/mod_session.phtml");
	include_once($ariadne."/includes/loader.web.php");

	function fix_quotes($input) {
		if (is_array($input)) {
			array_walk($input, "fix_quotes");
		} else {
			$input=stripslashes($input);
		}
		return $input;
	}


	$PATH_INFO=$HTTP_SERVER_VARS["PATH_INFO"];
	if (!$PATH_INFO) {

		ldRedirect($HTTP_SERVER_VARS["PHP_SELF"]."/");
		exit;

	} else {

		if (ini_get("safe_mode")) {
			error("Ariadne will not work correctly with safe_mode set to 'On'. Please edit your php.ini file and set it to 'Off'.");
		}
		if (Headers_sent()) {
			error("The loader has detected that PHP has already sent the HTTP Headers. This error is usually caused by trailing white space or newlines in the configuration files. See the following error message for the exact file that is causing this:");
			Header("Misc: this is a test header");
		}
		@ob_end_clean(); // just in case the output buffering is set on in php.ini, disable it here, as Ariadne's cache system gets confused otherwise. 
		// go check for a sessionid
		$root=$AR->root;
		$inst_store = $store_config["dbms"]."store";
		$store=new $inst_store($root,$store_config);
		$re="^/-(.*)-/";
		if (eregi($re,$PATH_INFO,$matches)) {
			$session_id=$matches[1];
			$PATH_INFO=substr($PATH_INFO,strlen($matches[0])-1);
			ldStartSession($session_id);
		}

		$AR->login="public";
		$split=strrpos($PATH_INFO, "/");
		$path=substr($PATH_INFO,0,$split+1);
		$function=substr($PATH_INFO,$split+1);
		if (!$function) {
			$function="view.html";
			$PATH_INFO.=$function;
		}
		$ldCacheFilename=strtolower($PATH_INFO)."=";
		if ($QUERY_STRING) {
			$ldCacheFilename.=$QUERY_STRING;
		}
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
		require($ariadne."/nls/".$nls);
		if (substr($function, -6)==".phtml") {
			// system template: no language check
			$ARCurrent->nolangcheck=1;
		}
		$cachedimage=$store_config["files"]."cache".$ldCacheFilename;
		$cachedheader=$store_config["files"]."cacheheaders".$ldCacheFilename;
		// yes, the extra '=' is needed, don't remove it. trust me.
		
		$timecheck=time();
		if (file_exists($cachedimage) && 
			(strpos($HTTP_SERVER_VARS["ALL_HTTP"],"no-cache") === false) &&
			(strpos($HTTP_PRAGMA,"no-cache") === false) &&
			(($mtime=filemtime($cachedimage))>$timecheck) &&
			($HTTP_SERVER_VARS["REQUEST_METHOD"]!="POST")) {

			$ctime=filectime($cachedimage);
			if ($HTTP_IF_MODIFIED_SINCE >= $ctime) {
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
				ldSetClientCache(true, $cachetime);
				if (file_exists($cachedheader)) {
					$headers=file($cachedheader);
					while (list($key, $header)=@each($headers)) {
						ldHeader(chop($header));
					}
				}
				readfile($cachedimage);
			}

		} else {

			// FIX magic_quoted input
			set_magic_quotes_runtime(0);
			$args=array_merge($HTTP_GET_VARS,$HTTP_POST_VARS);
			if (get_magic_quotes_gpc()) {
				$args=fix_quotes($args);
				$ARCookie=stripslashes($ARCookie);
			}
			
			$store->call($function, $args, $store->get($path));
			if (!$store->total) {
				$requestedpath=$path;
				while ($path!=$prevPath && !$store->exists($path)) {
					$prevPath=$path;
					$path=$store->make_path($path, "..");
				}
				if ($prevPath==$path) {
					error("Database is not initialised, please run <a href=\"".$AR->host.$AR->dir->www."install/install.php\">the installer</a>");
				} else {
					$store->call("user.notfound.html",
						 Array(	"arRequestedPath" => $requestedpath,
						 		"arRequestedTemplate" => $function ),
						 $store->get($path));
				}
			}
			$store->close();

		}
		if ($ARCurrent->session) {
			$ARCurrent->session->save();
		}
		// now check for outputbuffering
		if ($image=ob_get_contents()) {
			ob_end_flush();
			debug("loader: ob_end_flush()","all");
			if (is_array($ARCurrent->cache) && ($file=array_pop($ARCurrent->cache))) {
				error("cached() opened but not closed with savecache()");
			} else {
				ldSetCache($ldCacheFilename, $ARCurrent->cachetime, $image, @implode("\n",$ARCurrent->ldHeaders));
			}
		}
	}
?>