<?php
	// debugging functions.
	$DB["all"]=5;
	$DB["store"]=4;
	$DB["class"]=3;
	$DB["object"]=2;
	$DB["pinp"]=1;
	$DB["off"]=0;
	$DB["level"]=$DB["off"];
	$DB["file"]="/tmp/soap.log";
	$ERRMODE="htmljs"; // alternative: "text"/"html"/"js"

	function debug($text, $level="pinp", $indent="") {
	global $DB, $DB_INDENT;
	 	if ($DB["fp"] && $DB["level"]>=$DB[$level]) {
			if ($indent=="OUT") {
				$DB_INDENT=substr($DB_INDENT,0,-2);
			}
			fwrite($DB["fp"], "$DB_INDENT $level::$text\n");
			fflush($DB["fp"]);
			if ($indent=="IN") {
				$DB_INDENT.="  ";
			}
		}
	}

	function debugon($level="pinp") {
	global $DB;
		$DB["fp"]=fopen($DB["file"], "a+");
		if ($DB["fp"]) {
			$DB["level"]=$DB[$level];
			debug("Debuglevel: $level");
		}
	}

	function debugoff() {
	global $DB;
		if ($DB["fp"]) {
			debug("Debugging off.");
			$DB["level"]=$DB["off"];
			@fclose($DB["fp"]);
		}
	}

	function error($text, $code="Client.Unkown") {
		global $SOAP_Fault;
			debug("soap::ldObjectNotFound($requestedpath, $requestedtemplate)", "loader");
			$SOAP_Fault = new soap_fault(
							$code,
							$text,
							"Server could not find $requestedpath::$requestedtemplate");

		debug("error: '$text'");
	}

	function ldObjectNotFound($requestedpath, $requestedtemplate) {
	global $SOAP_Fault;
		debug("soap::ldObjectNotFound($requestedpath, $requestedtemplate)", "loader");
		$SOAP_Fault = new soap_fault(
						"Client.ObjectNotFound",
						"",
						"Server could not find $requestedpath::$requestedtemplate");
	}

	function ldAccessDenied($path, $message) {
	global $SOAP_Fault, $store;

		$SOAP_Fault = new soap_fault(
						"Client.AccessDenied",
						"",
						$message);
	}

	function ldSetRoot($session='', $nls='') {
	global $store, $AR, $ARCurrent, $root, $rootoptions;

		$root=$AR->root;
		$rootoptions="";
		if ($session) {
			$rootoptions.="/-".$session."-";
			$ARCurrent->session->id=$session;
		}
		if ($nls) {
			$rootoptions.="/$nls";
			$ARCurrent->nls=$nls;
		}
		$root.=$rootoptions;
		if ($store) { // loader.php uses this function before the store is initialized.
			$store->root=$root;
			$store->rootoptions=$rootoptions;
		}	
	}

	function ldSetNls($nls) {
	global $ARCurrent;

		$session=$ARCurrent->session->id;
		ldSetRoot($session, $nls);
	}

	function ldSetSession($session='') {
	global $ARCurrent, $ARLogin, $ARPassword;

		$nls=$ARCurrent->nls;
		ldSetRoot($session, $nls);
	}
 
	function ldStartSession($sessionid='') {
	global $ARCurrent, $AR, $ariadne;

		require($ariadne."/configs/sessions.phtml");
		$ARCurrent->session=new session($session_config,$sessionid);
		ldSetSession($ARCurrent->session->id);
	}

	function ldSetCache($file, $time, $image, $headers) {
	global $store;

		debug("ldSetCache($file, $time, [image], [headers])","object");
		debug("ldSetCache::not implemented\n");
	}

	function ldMkDir($dir) {
	global $store;

		debug("ldMkDir($dir)","object");
		$dir=strtok($dir, "/");
		$curr=$store->files;
		while ($dir) {
			$curr.=$dir."/";
			debug("ldMkDir: $curr","all");
			@mkdir($curr, 0755);
			$dir=strtok("/");
		}
	}

	function ldGetCredentials() {
		return false;
	}

	function ldSetCredentials($login, $password) {
	global $ARCurrent, $SOAP_SessionID;

		debug("ldSetCredentials($login, [password])","object");
		$ARCurrent->session->put("ARLogin", $login);
		$ARCurrent->session->put("ARPassword", $password, 1);
		$SOAP_SessionID = $ARCurrent->session->id.
							md5($ARCurrent->session->id.
									$login.$password);

		return $SOAP_SessionID;
	}

	function ldCheckCredentials($login, $password) {
	global $ARCurrent, $SOAP_SessionID;
		debug("ldCheckCredentials()","object");
		$result = false;
		if ($ARCurrent->session && $SOAP_SessionID) {
			$sessionid = $ARCurrent->session->id;
			$md5_hash = $sessionid.md5($sessionid.
						$ARCurrent->session->get("ARLogin").
						$ARCurrent->session->get("ARPassword",1)); 
			debug("soap:: checking ($md5_hash) against $SOAP_SessionID", "loader");
			if ($md5_hash == $SOAP_SessionID) {
				$result = true;
			}
		}
		return $result;
	}

	function ldRedirect($uri) {
		return ldHeader("Location: $uri");
	}

	function ldHeader($header) {
	global $ARCurrent;

		$result=false;
		if (!Headers_sent()) {
			$result=true;
			Header($header);
			$ARCurrent->ldHeaders[strtolower($header)]=$header;
		} else {
			debug("Headers already sent, couldn't send $header","all");
		}
		return $result;
	}

	function ldSetClientCache($cache_on, $expires=0, $modified=0) {
		global $ARCurrent;
		$now=time();
		$result = true;
		return $result;
	}

	function ldSetContent($mimetype, $size=0) {
		$result=ldHeader("Content-type: ".$mimetype);
		if ($size) {
			$result=ldHeader("Content-Length: ".$size);
		}
		return $result;
	}

?>