<?php
	$ARCurrent->nolangcheck=true;
//	if ($this->CheckLogin("delete") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.delete.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.delete.save.php"
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
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"save" => array(
					"value" => $ARnls["delete"]
				),
			);
		}

		$wgWizTitle=$ARnls["delete"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/delete.png';
		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
//	}
?>