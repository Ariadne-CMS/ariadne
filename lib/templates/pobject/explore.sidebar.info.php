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

		if( !$ARCurrent->arTypeNames ) {
			$this->call("typetree.ini");
		}

		$myType = ( $ARCurrent->arTypeNames[$this->type] ? $ARCurrent->arTypeNames[$this->type] : $this->type );

		$info = array();
		$info['type'] = yui::labelspan($myType);
		$info['size'] = $this->size;

		if ($this->CheckSilent("edit")) {
			$info['priority'] = "<a href=\"javascript:muze.ariadne.explore.arshow('edit_priority','" . $this->make_ariadne_url() . "dialog.priority.php')\" title=\"". $ARnls['change_priority'] . "\">" . $this->priority . "</a>";
		} else {
			$info['priority'] = $this->priority;
		}

		$info['ariadne:id'] = $this->id;

		$section = array(
			'id' => 'info',
			'label' => $ARnls['ariadne:info'],
			'details' => yui::section_table($info)
		);

		echo showSection($section);
	}
?>