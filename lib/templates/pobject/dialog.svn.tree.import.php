<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.svn.tree.import.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.svn.tree.import.save.php"
			)
		);

		$wgWizAction = $this->getdata("wgWizAction");
		
		if( $wgWizAction == "save" ) {
			$wgWizStyleSheets = array( $AR->dir->styles."svn.css" );
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["ok"]
				),
				"template" => array(
					"value" => $ARnls['back'],
					"location" => "left"
				)
			);
		} else {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"save" => array(
					"value" => $ARnls["ariadne:svn:import"]
				),
			);
		}

		$wgWizTitle=$ARnls['ariadne:svn:import_recursive'];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/svnimport.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
