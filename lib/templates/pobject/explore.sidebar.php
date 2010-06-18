<?php
	$ARCurrent->nolangcheck=true;    
	$ARCurrent->allnls=true;

	if ($this->CheckLogin("read") && $this->CheckConfig()) {

	  	include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);

		$this->call("explore.sidebar.functions.php", $arCallArgs);

		global $invisibleSections;
		if (!$arLanguage) {
			$arLanguage=$nls;
			if (is_array($arCallArgs)) {
				$arCallArgs["arLanguage"]=$nls;
				$arCallArgs["invisibleSections"]=$invisibleSections;
			} else {
				$arCallArgs.="&arLanguage=$nls&invisibleSections=$invisibleSections";
			}
		}
		if (isset($data->$arLanguage)) {
			$nlsdata=$data->$arLanguage;
		}

		//tasks
		if (!$ARCurrent->arTypeTree) {
			$this->call("typetree.ini");
		}
		$this->call("explore.sidebar.tasks.php", $arCallArgs);
		$this->call("explore.sidebar.settings.php", $arCallArgs);
		if ($AR->SVN->enabled) {
			$this->call("explore.sidebar.svn.php", $arCallArgs);
		}
		$this->call("explore.sidebar.info.php", $arCallArgs); 
		$this->call("explore.sidebar.details.php", $arCallArgs);
	}
?>