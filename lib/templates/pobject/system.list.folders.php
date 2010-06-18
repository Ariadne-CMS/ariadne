<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$arResult = $this->call("system.list.objects.php", array('type' => 'pdir'));
	}
?>
