<?php

$ARCurrent->nolangcheck=true;
if (($this->CheckLogin("edit") || $this->CheckLogin("add", ARANYTYPE)) && $this->CheckConfig()) {

	$wgWizFlow[] = array(
		"title" => $ARnls["date"],
		"image" => $AR->dir->images."wizard/schedule.png",
		"template" => "dialog.edit.date.php",
		"nolang" => true,
	);

	$wgWizFlow[] = array(
		"title" => $ARnls["repeat"],
		"image" => $AR->dir->images."wizard/scenario.png",
		"template" => "dialog.edit.repeat.php",
		"nolang" => true,
	);

	$arResult = $wgWizFlow;
}

?>