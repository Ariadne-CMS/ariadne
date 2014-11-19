<?php
	$ARCurrent->nolangcheck=true;
//	if ($this->CheckLogin("config") && $this->CheckConfig() && $this->can_mogrify() ) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.mogrify.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.mogrify.save.php"
			)
		);

		if ($wgWizAction == 'save') {
			$wgWizButtons = array(
				"template" => array(
					"value" => $ARnls["back"]
				)
			);
		} else {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"save" => array(
					"value" => $ARnls["ariadne:mogrify"]
				),
			);
		}

		$wgWizTitle=$ARnls["ariadne:mogrify"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/mogrify.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
//	}
?>
