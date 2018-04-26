<?php
	$ARCurrent->nolangcheck=true;
	$ARCurrent->allnls=true;

	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		if ($AR->user->data->language) {
			ldSetNls($AR->user->data->language);
		}

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

		$taskEventData = new baseObject();
		$taskEventData = ar_events::fire( 'ariadne:onbeforesidebartasks', $taskEventData );
		if ( $taskEventData ) {
			// Do the stuff you need to do.
			$this->call("explore.sidebar.tasks.php", $arCallArgs);
                        ar_events::fire('ariadne:onsidebartasks');
                }

		$settingsEventData = new baseObject();
		$settingsEventData = ar_events::fire( 'ariadne:onbeforesidebarsettings', $settingsEventData );
		if ( $settingsEventData ) {
			// Do the stuff you need to do.
			$this->call("explore.sidebar.settings.php", $arCallArgs);
			ar_events::fire( 'ariadne:onsidebarsettings' );
		}

		if (getenv("ARIADNE_WORKSPACE") && workspace::enabled($this->path)) {
			$this->call("explore.sidebar.workspace.php", $arCallArgs);
		}

		if ($AR->SVN->enabled) {
			$svnEventData = new baseObject();
			$svnEventData = ar_events::fire( 'ariadne:onbeforesidebarsvn', $svnEventData );
			if ($svnEventData) {
				$this->call("explore.sidebar.svn.php", $arCallArgs);
				ar_events::fire('ariadne:onsidebarsvn');
			}
		}
		$infoEventData = new baseObject();
		$infoEventData = ar_events::fire( 'ariadne:onbeforesidebarinfo', $infoEventData );
		if ($infoEventData) {
			$this->call("explore.sidebar.info.php", $arCallArgs);
			ar_events::fire('ariadne:onsidebarinfo');
		}

		$detailsEventData = new baseObject();
		$detailsEventData = ar_events::fire( 'ariadne:onbeforesidebardetails', $detailsEventData );
		if ($detailsEventData) {
			$this->call("explore.sidebar.details.php", $arCallArgs);
			ar_events::fire('ariadne:onsidebardetails');
		}

	}
?>
