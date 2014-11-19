<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.priority.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.priority.save.php"
			)
		);

		if ($wgWizAction == 'save') {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"template" => array(
					"value" => $ARnls['back']
				)
			);
		}

		$wgWizTitle = $ARnls["priority"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/priority.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
