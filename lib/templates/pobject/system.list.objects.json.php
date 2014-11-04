<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$folders = $this->call('system.list.objects.php', $arCallArgs);
		echo json_encode($folders);
	}

?>
