<?php
	require_once("./ariadne.inc");
	require_once($ariadne."/configs/ariadne.phtml");
	$PATH_INFO=$HTTP_SERVER_VARS["PATH_INFO"];
	$re="^/-(.*)-/";
	if (eregi($re,$PATH_INFO,$matches)) {
		$ldSessionId=$matches[1];
		$PATH_INFO=substr($PATH_INFO,strlen($matches[0])-1);
		$NEWPATH_INFO="/-$ldSessionId-";
	}
	$split=strpos(substr($PATH_INFO, 1), "/");
	$ldNLS=substr($PATH_INFO, 1, $split);
	if ($AR->nls->list[$ldNLS]) {
		// valid language
		$NEWPATH_INFO.="/$ldNLS";
		$PATH_INFO=substr($PATH_INFO, $split+1);
	}
	$NEWPATH_INFO.="/muze".$PATH_INFO;
	$HTTP_SERVER_VARS["PATH_INFO"]=$NEWPATH_INFO;
	include("./loader.php");
?>