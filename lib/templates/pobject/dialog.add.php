<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("add", ARANYTYPE) && $this->CheckConfig()) {

		include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizButtons = array(
			"cancel" => array(
				"value" => $ARnls["cancel"]
			),
		);

		$wgWizFlow = array();
		$wgWizFlow[] = array(
			"current" => $this->getdata("wgWizCurrent","none"),
			"cancel" => "window.close.js",
		);
		
		$wgWizFlow[] = array(
			"title" => $ARnls["typetree"],
			"image" => $AR->dir->images.'wizard/add_typetree.png',
			"template" => "dialog.add.typetree.php",
		);

		if( $this->CheckSilent("layout") ) {
			$wgWizFlow[] = array(
				"title" => $ARnls["all"],
				"image" => $AR->dir->images.'wizard/add_all.png',
				"template" => "dialog.add.all.php",
			);
		}
		
		$wgWizFlow = $this->call("user.wizard.add.html", Array("wgWizFlow" => $wgWizFlow));
		
		$wgWizStyleSheets = array(
			$AR->dir->styles."addobject.css",
		);
		// spawn wizard
		$wgWizHeaderIcon = $AR->dir->images . 'icons/large/add.png';
		$wgWizTitle=$ARnls["ariadne:new"];
		$wgWizHeader=$wgWizTitle;

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>