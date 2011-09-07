<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$arCallArgs['type'] = 'pdir';
		$arResult = $this->call("system.list.objects.php", $arCallArgs);
	}
?>
