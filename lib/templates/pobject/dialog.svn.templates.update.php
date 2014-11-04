<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.svn.templates.update.form.php",
				"cancel" => "window.close.js",
				"diff" => "dialog.svn.templates.serverdiff.save.php",
				"save" => "dialog.svn.templates.update.save.php"
			)
		);

		$wgWizAction = $this->getdata("wgWizAction");

		if( $wgWizAction == "save" ) {
			$wgWizStyleSheets = array( $AR->dir->styles."svn.css" );

			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["ok"]
				)
			);
		} else if ($wgWizAction == "diff") {
			$wgWizStyleSheets = array( $AR->dir->styles."svn.css" );
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"save" => array(
					"value" => $ARnls["ariadne:svn:update"]
				),
			);
		} else {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"diff" => array(
					"value" => $ARnls["ariadne:svn:update"]
				),
			);
		}

		$wgWizTitle=$ARnls['ariadne:svn:update'];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/svnupdate.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
