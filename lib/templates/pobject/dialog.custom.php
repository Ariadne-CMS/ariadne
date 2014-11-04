<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.custom.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.custom.save.php"
			)
		);

		$wgWizAction = $this->getdata("wgWizAction");

		if( $wgWizAction == "save" ) {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["ok"]
				)
			);
		} else {
			$wgWizButtons = array(
/*				"remove" => array(
					"value" => $ARnls["remove"],
					"location" => "left"
				),
				"add" => array(
					"value" => $ARnls["add"],
					"location" => "left"
				),
*/				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"save" => array(
					"value" => $ARnls["ok"]
				),
			);
		}

		$wgWizTitle=$ARnls["customdata"];

		$wgWizHeader=$wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/customfields.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>