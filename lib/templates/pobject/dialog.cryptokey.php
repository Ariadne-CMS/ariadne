<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.cryptokey.form.php",
				"cancel" => "window.close.js",
			)
		);

		$wgWizButtons = array(
			"cancel" => array(
				"value" => $ARnls["ariadne:close"]
			)
		);

		$wgWizTitle = $ARnls["ariadne:cryptokey"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/cryptos.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
