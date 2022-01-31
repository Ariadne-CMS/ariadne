<?php
$ARCurrent->nolangcheck = true;
$ARCurrent->allnls = true;

if ($this->CheckLogin("read") && $this->CheckConfig()) {

	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {

		if ($AR->user->data->language) {
			ldSetNls($AR->user->data->language);
		}

		global $invisibleSections;
		if (!isset($arLanguage) || !$arLanguage) {
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

		$arCallArgs["shortcutSidebar"] = true;

		//tasks
		if( !isset($ARCurrent->arTypeTree) ) {
			$this->call("typetree.ini");
		}

		// tasks on the target
		$path = $this->store->make_path( $this->parent, $this->data->path);
		$this->get($path, "explore.sidebar.tasks.php", $arCallArgs);
		$this->get($path, "explore.sidebar.settings.php", $arCallArgs);

		$arCallArgs["shortcutSidebar"] = false;
		//tasks on the shortcut
		$this->call("explore.sidebar.tasks.php", $arCallArgs);
		$this->call("explore.sidebar.settings.php", $arCallArgs);
		if ($AR->SVN->enabled) {
			$this->call("explore.sidebar.svn.php", $arCallArgs);
		}
		$this->call("explore.sidebar.info.php", $arCallArgs);
		$this->call("explore.sidebar.details.php", $arCallArgs);
	}
}
?>
