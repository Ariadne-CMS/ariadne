<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.workspace.form.php",
				"cancel" => "window.close.js",
				"commit" => "dialog.workspace.commit.php",
				"revert" => "dialog.workspace.revert.php"
			)
		);

		$wgWizButtons = array(
			"cancel" => array(
				"value" => $ARnls["cancel"]
			),
			"revert" => array(
				"value" => $ARnls["ariadne:workspace:revert_selected"]
			),
			"commit" => array(
				"value" => $ARnls["ariadne:workspace:commit_selected"]
			)
		);

		$wgWizTitle=$ARnls["ariadne:workspace:manage_workspace"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/svndiff.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
