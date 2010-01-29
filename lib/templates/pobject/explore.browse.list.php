<?php 
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$arCallArgs["viewtype"] = "list";
		$this->call("explore.browse.php", $arCallArgs);
	}
?>
