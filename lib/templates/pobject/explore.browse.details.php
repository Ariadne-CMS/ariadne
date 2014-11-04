<?php
	$ARCurrent->nolangcheck = true;
	$ARCurrent->allnls = true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$arCallArgs["viewtype"] = "details";
		$this->call("explore.browse.php", $arCallArgs);
	}
?>
