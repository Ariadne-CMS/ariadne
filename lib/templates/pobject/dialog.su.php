<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("admin") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getvar("wgWizCurrent","none"),
				"template" => "dialog.su.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.su.save.php"
			)
		);
		
		if ($wgWizAction == 'save') {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
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
					"value" => $ARnls["ariadne:su"]
				),
			);
		}				

		$wgWizTitle=$ARnls["ariadne:su"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/grants.png';

		$wgWizBufferOutput = true; // Allow sending headers
		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>