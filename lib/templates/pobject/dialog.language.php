<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.language.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.language.save.php"
			)
		);

		$wgWizTitle=$ARnls["language"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/language.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
