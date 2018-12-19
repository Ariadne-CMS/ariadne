<?php
	if ( $this->CheckLogin('add', $arNewType) && $this->CheckConfig() ) {
		echo '<fieldset class="editdata">';
		echo '<legend>';
		if (sizeof($arLanguages) > 1) {
			echo $ARnls['ariadne:new:default_language_data'].' : '.$ARConfig->nls->list[$arLanguage];
		} else {
			echo $ARnls['data'];
		}
		echo '</legend>';
		$arNewData=new baseObject;
		$arNewPath=$this->make_path($arNewFilename);
		$wgWizCallObject=$this->store->newobject($arNewPath, $this->path, $arNewType, $arNewData);
		$wgWizCallObject->arIsNewObject=true;
		$wgWizCallObject->error = $this->error;

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
			"title" => $ARnls["ariadne:new:filename"],
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
		if ( false && $hascustomdata ) { // Skip custom data for now, it doesn't play nice with the rest.
			$wgWizFlow[] = array(
				"title" => $ARnls["customdata"],
				"image" => $AR->dir->images.'wizard/customdata.png',
				"template" => "dialog.edit.custom.php"
			);
		}

		// call user overridable new flow
		$wgWizFlow = $wgWizCallObject->call("user.wizard.new.html", array("wgWizFlow" => $wgWizFlow));

		$showLanguageSelect = false;
		foreach ($wgWizFlow as $step) {
			if ($step['title']) {
				echo '<fieldset class="flowStep"><legend>' . $step['title'] . '</legend></fieldset>';
			}
			if ($step['template']) {
				$wgWizCallObject->call($step['template'], array("arNewType" => $arNewType, "arLanguage" => $arLanguage));
				if (!$step['nolang']) {
					$showLanguageSelect = true;
				}
			}
		}

		echo '</fieldset>';

		$languageList = $ARConfig->nls->list;
		$usedLanguages = $arLanguages;
		foreach ( $usedLanguages as $language ) {
			unset($languageList[$language]);
		}

		foreach ( $usedLanguages as $extraLanguage ) {
			echo '<input type="hidden" name="arLanguages[]" value="' . htmlspecialchars($extraLanguage) . '">';
			if ( $extraLanguage != $arLanguage ) {
				echo '<fieldset class="editdata">';
				echo '<legend>' . $ARnls['data'] . ' : ' . $ARConfig->nls->list[$extraLanguage] .'</legend>';
				foreach ($wgWizFlow as $step) {
					if ($step['template'] && !$step['nolang']) {
						$wgWizCallObject->call($step['template'], array("arNewType" => $arNewType, "arLanguage" => $extraLanguage));
					}
				}
				echo '</fieldset>';
			}
		}

		if ($showLanguageSelect && sizeof($languageList) > 0) {
			$fields = array(
				"arLanguages" => array(
					"type" => "select",
					"options" => $languageList,
					"label" => false,
					"name" => "extraLanguage",
				),
				"addLanguageHidden" => array(
					"type" => "html",
					"value" => "<input type='hidden' name='addLanguage' value=''>",
					"label" => false
				),
				"addLanguage" => array(
					"type" => "button",
					"value" =>
					$ARnls['ariadne:new:add_language'],
					"label" => false,
					"class" => "addLanguage"
				)
			);
			echo '<fieldset id="languages">';
			echo '<legend>' . $ARnls['ariadne:new:extralanguages'] . '</legend>';
			echo ar('html')->form($fields, false)->getHTML()->childNodes;
			echo '</fieldset>';
		}

	}
?>
