<?php 
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$arCallArgs["viewtype"] = "icons";
		$this->call("explore.browse.php", $arCallArgs);
	}
?>
