<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"save" => "dialog.preferences.save.php",
				"cancel" => "window.close.js"
			),
			array(
				"title" => $ARnls["language"],
				"image" => $AR->dir->images."wizard/languages.png",
				"template" => "dialog.preferences.language.php"
			),
			array(
				"title" => $ARnls["interface"],
				"image" => $AR->dir->images."wizard/customdata.png",
				"template" => "dialog.preferences.interface.php",
			),
			array(
				"title" => $ARnls["editor"],
				"image" => $AR->dir->images."wizard/customdata.png",
				"template" => "dialog.preferences.editor.php",
			),
		);

		$wgWizTitle=$ARnls["preferences"];
		$wgWizHeader=$ARnls["preferences"];
		$wgWizHeaderIcon = $AR->dir->images."icons/large/preferences.png";

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
