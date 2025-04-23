<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.import.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.import.save.php",
				"done" => "window.close.objectadded.js"
			)
		);

		$wgWizAction = $this->getdata("wgWizAction");
		if (($_SERVER["CONTENT_LENGTH"] ?? null) && !$wgWizAction) {
			$this->error = $ARnls["err:fileupload"];
		}

		if( $wgWizAction == "save" ) {
			$wgWizButtons = array(
				"done" => array(
					"value" => $ARnls["ok"]
				)
			);
		} else {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"save" => array(
					"value" => $ARnls["ariadne:import"]
				),
			);
		}
		$wgWizTitle=$ARnls["importobject"];

		$wgWizHeader=$wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/import.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
