<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.svn.tree.diff.form.php",
				"cancel" => "window.close.js",
			)
		);

		$wgWizButtons = array(
			"cancel" => array(
				"value" => $ARnls["ok"]
			)
		);

		$wgWizTitle=$ARnls['ariadne:svn:diff'];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/svndiff.png';

		$wgWizStyleSheets = array( $AR->dir->styles."svn.css" );

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
