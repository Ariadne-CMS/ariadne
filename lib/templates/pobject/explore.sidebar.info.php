<?php
	$ARCurrent->nolangcheck=true;
	include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);
	include_once($this->store->get_config("code")."modules/mod_yui.php");
	
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		if (!$arLanguage) {
			$arLanguage=$nls;
			if (is_array($arCallArgs)) {
				$arCallArgs["arLanguage"]=$nls;
			} else {
				$arCallArgs.="&arLanguage=$nls";
			}
		}

		// FIXME: should take this information from sys.object.list.entry.html

		$myType = $this->get("/system/ariadne/types/".$this->type, "system.get.name.phtml");
		if (!$myType) {
			$myType = array($this->type);
		}

		$info = array();
		$info['type'] = yui::labelspan($myType[0]);
		$info['size'] = $this->size;

		$info['priority'] = $this->priority;
		if ($this->CheckSilent("edit")) {
			$info['priority'] = "<a href=\"javascript:muze.ariadne.explore.arshow('edit_priority','" . $this->make_local_url() . "dialog.priority.php')\" title=\"". $ARnls['change_priority'] . "\">" . $this->priority . "</a>";
		}

		$info['ariadne:id'] = $this->id;

/*		$details = '<strong>' . $myName . '</strong><br>';
		$details .= $myType[0];
		$details .= '<br>';
*/

		$details .= yui::section_table($info);
		$section = array(
			'id' => 'info',
			'label' => $ARnls['ariadne:info'],
			'details' => $details
		);

		echo showSection($section);
	}
?>