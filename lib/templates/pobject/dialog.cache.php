<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.cache.form.php",
				"save"	=> "dialog.cache.save.php",
				"cancel" => "window.close.js"
			)
		);

		// no wgWizButtons set, we're defaulting

		$wgWizTitle=$ARnls["caching"];
   		$wgWizHeader=$wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/cache.png';

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>