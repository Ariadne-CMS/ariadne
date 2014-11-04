<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.locks.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.locks.save.php"
			)
		);

		$wgWizAction = $this->getdata("wgWizAction");

		$locks=$this->store->mod_lock->get_locks($this->data->login);
		if (!count($locks)) {
			$wgWizAction = "save";
		}


		if($wgWizAction == "save" ) {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["ok"]
				)
			);
		} else {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"save" => array(
					"value" => $ARnls["apply"]
				),
			);
		}

		$spath = $AR->dir->www . "js/yui/";
		$wgWizStyleSheets = array(
			$spath."datatable/assets/skins/sam/datatable.css",
			$AR->dir->www . "styles/locks.css"
		);

		$wgWizScripts = array(
			$spath."element/element-min.js",
			$spath."datasource/datasource-min.js",
			$spath."datatable/datatable-min.js"
		);

		$wgWizTitle = sprintf($ARnls["openlocks"], $nlsdata->name);
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/grants.png';
		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>