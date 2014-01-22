<?php
	$ARCurrent->nolangcheck=true;
	//if ($this->CheckLogin("delete") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.path.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.rename.save.php"
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
		} else {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"save" => array(
					"value" => $ARnls["move"]
				),
			);
       		}
         
		$wgWizTitle = $ARnls["move"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/customfields.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	//}
?>