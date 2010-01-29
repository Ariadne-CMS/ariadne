<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		require_once($this->store->get_config("code")."modules/mod_json.php");
		$folders = $this->call('system.list.folders.php');
		echo JSON::encode($folders);
	}
		
?>
