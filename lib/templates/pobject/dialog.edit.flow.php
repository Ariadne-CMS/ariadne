<?php
	$ARCurrent->nolangcheck=true;
	// empty, do nothing for pobject.
	if (($this->CheckLogin("edit") || $this->CheckLogin("add", ARANYTYPE)) && $this->CheckConfig()) {
		$arResult = $wgWizFlow;
	}
?>
