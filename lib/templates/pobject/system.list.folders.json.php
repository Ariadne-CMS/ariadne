<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$folders = $this->call('system.list.folders.php');
		echo json_encode($folders);
	}
		
?>
