<?php

$ARCurrent->nolangcheck=true;
if ($this->CheckLogin("edit") && $this->CheckConfig()) {

	$wgWizFlow[] = array(
		"title" => $ARnls["display"],
		"image" => $AR->dir->images."wizard/data.png",
		"template" => "dialog.edit.displays.php",
		"nolang" => true,
	);
	
	$arResult = $wgWizFlow;
}

?>