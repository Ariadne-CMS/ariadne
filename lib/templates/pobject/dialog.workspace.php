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
				"revert" => "dialog.workspace.revert.php",
				"diff" => "dialog.workspace.diff.php"
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
			),
			"diff" => array(
				"value" => $ARnls["ariadne:workspace:diff_selected"]
			)
		);

		$wgWizTitle=$ARnls["ariadne:workspace:manage_workspace"];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/svndiff.png';

		$yui_base = $AR->dir->www."js/yui/";
		$wgWizStyleSheets = array(
			$yui_base . "datatable/assets/skins/sam/datatable.css",
			$yui_base . "menu/assets/skins/sam/menu.css",
			$yui_base . "container/assets/skins/sam/container.css",
			$AR->dir->styles."templates.css",

		);

		$wgWizScripts = array(
				$yui_base . "element/element-min.js",
				$yui_base . "datasource/datasource-min.js",
				$yui_base . "datatable/datatable-min.js",
				$yui_base . "container/container_core-min.js"
		);
		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
