<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {

		include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizButtons = array(
			"cancel" => array(
				"value" => $ARnls["cancel"]
			),
			"0" => array( // 0 means submit, but no action -> form shown again
				"value" => $ARnls["search"]
			),
		);
		
		$wgWizFlow = array();
		$wgWizFlow[] = array(
			"current" => $this->getdata("wgWizCurrent","none"),
			"cancel" => "window.close.js",
		);
		
		$wgWizFlow[] = array(
			"title" => $ARnls["contextsearch"],
			"image" => $AR->dir->images.'wizard/data.png',
			"template" => "dialog.search.context.php",
		);

		$wgWizFlow[] = array(
			"title" => $ARnls["advancedsearch"],
			"image" => $AR->dir->images.'wizard/data.png',
			"template" => "dialog.search.advanced.php",
		);


		// spawn wizard
		$wgWizHeaderIcon = $AR->dir->images . 'icons/large/search.png';
		$wgWizTitle=$ARnls["search"];
		$wgWizHeader=$wgWizTitle;

		$spath = $AR->dir->www . "js/yui/";
		$wgWizStyleSheets = array(
			$spath."datatable/assets/skins/sam/datatable.css",
		);
		
		$wgWizScripts = array(
			$spath."element/element-min.js",
			$spath."datasource/datasource-min.js",
			$spath."datatable/datatable-min.js"
		);

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>