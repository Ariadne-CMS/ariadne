<?php
	$ARCurrent->nolangcheck=true;
	// empty, do nothing for pobject.
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$arResult = $wgWizFlow;
	} 
?>