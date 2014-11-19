<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("config") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.grantkey.form.php",
				"cancel" => "window.close.js",
				"generate" => "dialog.grantkey.form.php"
			)
		);

		$wgWizButtons = array(
			"cancel" => array(
				"value" => $ARnls["ariadne:close"]
			)
		);
		if ($AR->sgSalt) {
			$wgWizButtons["generate"] = array(
				"value" => $ARnls["ariadne:generate"]
			);
		}

		$wgWizTitle = $ARnls["ariadne:grantkey"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/grants.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
