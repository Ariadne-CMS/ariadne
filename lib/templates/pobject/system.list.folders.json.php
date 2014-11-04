<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$folders = $this->call('system.list.folders.php', $arCallArgs);
		echo json_encode($folders);
	}

?>
