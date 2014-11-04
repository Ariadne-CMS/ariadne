<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$configcache=$ARConfig->cache[$this->path];
		if ($configcache->custom) {
			foreach ($configcache->custom as $key => $definition ) {
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
			"save" => "dialog.edit.shortcut.save.php"
		);
		$wgWizFlow[] = array(
			"title" => $ARnls["data"],
			"image" => $AR->dir->images.'wizard/data.png',
			"template" => "dialog.edit.shortcut.form.php"
		);

		// Custom data and locking gets added last.
		if( $hascustomdata ) {
			$wgWizFlow[] = array(
				"title" => $ARnls["customdata"],
				"image" => $AR->dir->images.'wizard/customdata.png',
				"template" => "dialog.edit.shortcut.custom.php"
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
		// call user overridable flow -- Removed, this breaks things
		$wgWizFlow = $this->call("user.wizard.edit.shortcut.html", Array("wgWizFlow" => $wgWizFlow));

		$this->call("typetree.ini");
		$name=$ARCurrent->arTypeNames[$this->type];

		// spawn wizard
		$wgWizHeaderIcon = $this->call("system.get.icon.php");
		$wgWizTitle=$ARnls["edit"]." ".$name;
		$wgWizHeader=$wgWizTitle;
		$wgWizTabsTemplate="dialog.edit.languagetabs.php";

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>