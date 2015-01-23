<?php
	ldHeader('X-XSS-Protection: 0');
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.templates.edit.form.php",
				"cancel" => "dialog.templates.edit.cancel.php",
				"save" => "dialog.templates.edit.save.php",
				"apply" => "dialog.templates.edit.apply.php",
				"delete" => "dialog.templates.edit.delete.php",
			)
		);

		$wgWizButtons = array(
			"cancel" => array(
				"value" => $ARnls["cancel"],
				"location" => "left"
			),
			"delete" => array(
				"value" => $ARnls["delete"],
				"location" => "left"
			),
			"apply" => array(
				"value" => $ARnls["apply"]
			),
			"save" => array(
				"value" => $ARnls["save"]
			),
		);

		$wgWizTitle=sprintf($ARnls["edittemplate"],$this->path.($function ? $function : $ARnls["new"]));
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/templates.png';

		$yui_base = $AR->dir->www."js/yui/";
		$wgWizStyleSheets = array(
//			$yui_base . "fonts/fonts-min.css",
			$yui_base . "datatable/assets/skins/sam/datatable.css",
			$yui_base . "menu/assets/skins/sam/menu.css",
			$yui_base . "container/assets/skins/sam/container.css",
			$AR->dir->styles."templates.css",

		);
		$wgWizScripts = array(
				$yui_base . "element/element-min.js",
				$yui_base . "datasource/datasource-min.js",
				$yui_base . "datatable/datatable-min.js",
				$yui_base . "container/container_core-min.js",
				$yui_base . "menu/menu-min.js",
				$AR->dir->www . "js/muze.js",
				$AR->dir->www . "js/muze/event.js",
				$AR->dir->www . "js/muze/dialog.js",
				$AR->dir->www . "js/muze/util/textarea.js",
				$AR->dir->www . "js/muze/ariadne/templates.js",
				$AR->dir->www . "js/muze/ariadne/cookie.js",
				$AR->dir->www . "js/muze/util/splitpane.js",
				$AR->dir->www . "js/muze/util/pngfix.js",
				$AR->dir->www . "js/muze/ariadne/registry.js",
				$AR->dir->www . "js/muze/ariadne/explore.js", // Used by the window.open items in the head menu.
		);

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
