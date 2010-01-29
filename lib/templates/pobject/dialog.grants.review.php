<?php
//	include_once($this->store->get_config("code")."modules/mod_grant.php");
	include_once("dialog.grants.logic.php");

	$data = $this->getdata('data');
	echo "Notice: This information is here for debugging purposes. This screen should be removed in the 2.6.2 release version.<br><br>The grants have not been set - if you are satisfied with the changes, please press 'Apply' once more to set the grants.";
	echo "<pre>";
	print_r($data);
	echo "</pre>";
?>