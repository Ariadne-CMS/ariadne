<?php
	$ARCurrent->nolangcheck=true;
//	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.export.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.export.save.php"
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
					"value" => $ARnls["ariadne:export"]
				),
			);
		}

		$wgWizTitle=$ARnls["ariadne:export"]." ".$nlsdata->name;

		$wgWizHeader=$wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/export.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
//	}
?>
