<?php
	include("../ariadne.inc");
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"ariadne.phtml\"");
	$fh = fopen($ariadne . "/../files/temp/ariadne.phtml", 'r');
	fpassthru($fh);
	fclose($fh);
?>
