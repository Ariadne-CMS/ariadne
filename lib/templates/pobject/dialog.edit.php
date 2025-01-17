<?php
	$ARCurrent->nolangcheck=true;
	$ARCurrent->allnls = true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$configcache=$ARConfig->cache[$this->path];
		if ($configcache->custom ?? null) {
			foreach( $configcache->custom as $key => $definition ) {
				if (($definition["type"]==$this->type) ||
					($definition["inherit"] && ($this->implements($definition["type"])))) {
					$hascustomdata=true;
					break;
				}
			}
		}

		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array();
		$wgWizFlow[] = array(
			"current" => $this->getdata("wgWizCurrent","none"),
			"cancel" => "dialog.edit.cancel.php",
			"save" => "dialog.edit.save.php"
		);
		$wgWizFlow[] = array(
			"title" => $ARnls["data"],
			"image" => $AR->dir->images.'wizard/data.png',
			"template" => "dialog.edit.form.php"
		);

		// Call edit flow which will add the remaining flow to the wizard if appropriate
		$wgWizFlow = $this->call("dialog.edit.flow.php", array( "wgWizFlow" => $wgWizFlow ));


		// Custom data and locking gets added last.
		if( $hascustomdata ?? null ) {
			$wgWizFlow[] = array(
				"title" => $ARnls["customdata"],
				"image" => $AR->dir->images.'wizard/customdata.png',
				"template" => "dialog.edit.custom.php"
			);
		}
		if( !$this->lock('O')){
			$i = $wgWizFlow[0]["current"]+1;
			$wgWizFlow[$i]["template"] = "dialog.edit.lock.php";
		}

		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) { // language select hasn't been done yet, so start with default language
			$arLanguage=$ARConfig->nls->default;
			$ARCurrent->arLanguage=$arLanguage;
		}
		// call user overridable flow
		$ARCurrent->allnls = true;
		$wgWizFlow = $this->call("user.wizard.edit.html", array("wgWizFlow" => $wgWizFlow));

		$this->call("typetree.ini");
		$name=$ARCurrent->arTypeNames[$this->type] ?? $this->type;

		// spawn wizard
		$wgWizHeaderIcon = $this->call("system.get.icon.php");
		$wgWizTitle=$ARnls["edit"]." ".$name;
		$wgWizHeader=$wgWizTitle;
		$wgWizTabsTemplate="dialog.edit.languagetabs.php";

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
