<?php
	$ARCurrent->nolangcheck=true;
	include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);
	require_once($this->store->get_config("code")."modules/mod_yui.php");

	if ($this->CheckLogin("read") && $this->CheckConfig()) {

		if( !$ARCurrent->arTypeTree ) {
			$this->call("typetree.ini");
		}

		$myType = ( $ARCurrent->arTypeNames[$this->type] ? yui::labelspan($ARCurrent->arTypeNames[$this->type]) . "<br>" . yui::labelspan("(" . $this->type . ")") : yui::labelspan($this->type) );

		$info = array();
		$info['type'] = $myType;
		$info['target'] = yui::labelspan($this->data->path);

		$info['size'] = $this->size;

		if ($this->CheckSilent("edit")) {
			$info['priority'] = "<a href=\"javascript:muze.ariadne.explore.arshow('dialog.priority','" . $this->make_ariadne_url() . "dialog.priority.php')\" title=\"". $ARnls['ariadne:change_priority'] . "\">" . (isset($this->priority) ? $this->priority : "--") . "</a>";
		} else {
			$info['priority'] = (isset($this->priority) ? $this->priority : "--");
		}

		$info["ariadne:id"] = $this->id;

		$section = array(
			'id' => 'info',
			'label' => $ARnls['ariadne:info'],
			'details' => yui::section_table($info)
		);

		echo yui::getSection($section);
	}
?>
