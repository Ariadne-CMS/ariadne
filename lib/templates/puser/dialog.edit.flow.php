<?php

$ARCurrent->nolangcheck=true;
if (($this->CheckLogin("edit") || $this->CheckLogin("add", ARANYTYPE)) && $this->CheckConfig()) {

	$login = $this->getdata("login", "none");
	$arNewFilename = basename($this->getdata("arNewFilename", "none"));
	if (!$login && $arNewFilename && !preg_match('/\{.*\}/i', $arNewFilename)) {
		$this->data->login = $arNewFilename;
	}

	$wgWizFlow[1]["nolang"] = true; // no language tabs on first form

	$wgWizFlow[] = array(
		"title" => $ARnls["preferences"],
		"image" => $AR->dir->images."wizard/data.png",
		"template" => "dialog.edit.preferences.php",
		"nolang" => true,
	);
	
	if ($this->CheckSilent("config")) {
		$wgWizFlow[] = array(
			"title" => $ARnls["groups"],
			"image" => $AR->dir->images."wizard/data.png",
			"template" => "dialog.edit.groups.php",
			"nolang" => true,
		);
	}
	
	$arResult = $wgWizFlow;
}

?>