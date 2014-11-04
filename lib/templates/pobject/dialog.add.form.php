<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		require_once($this->store->get_config("code")."modules/mod_yui.php");

		$showall = $this->getvar('showall');
		if (!($showall && $this->CheckSilent("layout"))) {
			$showall = 0;
		}

		$location = $this->getvar('location');
		if (!$location) {
			$location = $this->path;
		}

		$arNewType = $this->getvar('arNewType');
		$arNewFilename = $this->getvar('arNewFilename');
		if (!$arNewFilename) {
			$arNewFilename = "{5:id}";
		}
		$arLanguages = $this->getvar('arLanguages');

		if (!$arLanguages) {
			$arLanguages = array(
				$ARConfig->nls->default
			);
		} else {
			$arLanguages[] = $ARConfig->nls->default;
		}

		$addLanguage = $this->getvar('addLanguage');
		$extraLanguage = $this->getvar('extraLanguage');
		if ($addLanguage && $extraLanguage) {
			$arLanguages[] = $extraLanguage;
		}
		$arLanguages = array_unique($arLanguages);

		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) { // language select hasn't been done yet, so start with default language
			$arLanguage=$ARConfig->nls->default;
			$ARCurrent->arLanguage=$arLanguage;
		}

		$this->call('dialog.add.form.location.php', array(
			'location' => $location
		));

		$this->call('dialog.add.form.type.php', array(
			'location' => $location,
			'showall' => $showall,
			'arNewType' => $arNewType,

		));

		if ($arNewType) {

			$this->call('dialog.add.form.data.php', array(
				'arLanguages' => $arLanguages,
				'arLanguage' => $arLanguage,
				'arNewFilename' => $arNewFilename,
				'arNewType' => $arNewType
			));

		}
	}
?>