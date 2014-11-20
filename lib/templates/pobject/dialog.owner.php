<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("config") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.owner.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.owner.save.php"
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
					"value" => $ARnls["change_owner"]
				),
			);
		}

		$wgWizTitle=$ARnls["change_owner"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/grants.png'; // FIXME: Should probably be owner.png, but that isn't there.

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
