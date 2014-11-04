<?php
	$ARCurrent->nolangcheck = true;
	if ( $this->CheckLogin("add", ARANYTYPE) && $this->CheckConfig() ) {
		include( $this->store->get_config("code")."widgets/wizard/code.php" );

		$this->call('system.save.tempfile.phtml');

		$wgWizFlow = array(
			array(
				"current"  => $this->getvar("wgWizCurrent","none"),
				"template" => "dialog.add.form.php",
				"cancel"   => "window.close.js",
				"save"     => "dialog.add.save.php"
			)
		);

		if ( $wgWizAction == 'save' ) {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"template" => array(
					"value" => $ARnls["back"]
				)
			);
		} else {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"save" => array(
					"value" => $ARnls["add"]
				),
			);
		}

		$wgWizTitle = $ARnls["add"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/add.png';
		$wgWizStyleSheets = array( $AR->dir->styles.'dialog.add.css' );
		include( $this->store->get_config("code")."widgets/wizard/yui.wizard.html" );
	}
?>