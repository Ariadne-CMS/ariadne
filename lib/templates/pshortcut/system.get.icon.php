<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$arResult = $this->call("pobject::system.get.icon.php", $arCallArgs);
	}
?>
