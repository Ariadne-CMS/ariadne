<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getvar("wgWizCurrent","none"),
				"template" => "dialog.rewrite.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.rewrite.save.php"
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
					"value" => $ARnls["ariadne:rewrite"]
				),
			);
		}

		$wgWizTitle=$ARnls["ariadne:rewrite"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/mogrify.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>