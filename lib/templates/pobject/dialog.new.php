<?php
	$ARCurrent->nolangcheck=true;

	$arNewType=$this->getdata("arNewType","none");
	if (!$arNewType) {
		error($ARnls["err:nonewtype"]);
		$this->store->close();
		exit();
	}

	if ($this->CheckLogin("add", $arNewType) && $this->CheckConfig()) {

		$arNewFilename=$this->getdata("arNewFilename","none");
		$arNewData=new baseObject;
		$arNewPath=$this->make_path($arNewFilename);
		$wgWizCallObject=$this->store->newobject($arNewPath, $this->path, $arNewType, $arNewData);
		$wgWizCallObject->arIsNewObject=true;
		$hascustomdata=false;

		$configcache=$ARConfig->cache[$this->path]; // use parent path
		if ($configcache->custom) {
			foreach( $configcache->custom as $key => $definition ) {
				if (($definition["type"]==$arNewType) || // use new type
					($definition["inherit"] && ($wgWizCallObject->AR_implements($definition["type"])))) { // check new object
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
		// inject filename step
		$wgWizFlow[] = array(
			"title" => $ARnls["filename"],
			"image" => $AR->dir->images.'wizard/info.png',
			"template" => "dialog.new.filename.php",
			"nolang" => true
		);

		$wgWizFlow[] = array(
			"title" => $ARnls["data"],
			"image" => $AR->dir->images.'wizard/data.png',
			"template" => "dialog.edit.form.php"
		);

		// Call edit flow which will add the remaining flow to the wizard if appropriate
		$wgWizFlow = $wgWizCallObject->call("dialog.edit.flow.php", array( "wgWizFlow" => $wgWizFlow ));
		// Call new flow which will can override the edit flow
		$wgWizFlow = $wgWizCallObject->call("dialog.new.flow.php", array( "wgWizFlow" => $wgWizFlow ));

		// Custom data and locking gets added last.
		if( $hascustomdata ) {
			$wgWizFlow[] = array(
				"title" => $ARnls["customdata"],
				"image" => $AR->dir->images.'wizard/customdata.png',
				"template" => "dialog.edit.custom.php"
			);
		}

		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) { // language select hasn't been done yet, so start with default language
			$arLanguage=$ARConfig->nls->default;
			$ARCurrent->arLanguage=$arLanguage;
		}
		// call user overridable new flow
		$wgWizFlow = $wgWizCallObject->call("user.wizard.new.html", array("wgWizFlow" => $wgWizFlow));

		$this->call("typetree.ini");
		$name=$ARCurrent->arTypeNames[$wgWizCallObject->type];

		// spawn wizard
		$wgWizHeaderIcon = $wgWizCallObject->call("system.get.icon.php");
		$wgWizTitle=$ARnls["new"]." ".$name;
		$wgWizHeader=$wgWizTitle;
		$wgWizTabsTemplate="dialog.edit.languagetabs.php";

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
