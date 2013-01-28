<?php
	$ARCurrent->nolangcheck=true;
	require_once($this->store->get_config("code")."modules/mod_yui.php");
	
	if ($this->CheckLogin("read") && $this->CheckConfig()) {

		if( !$ARCurrent->arTypeNames ) {
			$this->call("typetree.ini");
		}

                $myType = ( $ARCurrent->arTypeNames[$this->type] ? yui::labelspan($ARCurrent->arTypeNames[$this->type]) . "<br>" . yui::labelspan("(" . $this->type . ")") : yui::labelspan($this->type) );

		$info = array(
			'type' => $myType,
			'size' => $this->make_filesize($this->call("system.get.size.phtml")),
			'priority' => isset($this->priority) ? $this->priority : '--',
			'ariadne:id' => $this->id
		);

		if ($this->CheckSilent("edit")) {
			$info['priority'] = "<a href=\"javascript:muze.ariadne.explore.arshow('dialog.priority','" . $this->make_ariadne_url() . "dialog.priority.php')\" title=\"". $ARnls['ariadne:change_priority'] . "\">" . (isset($this->priority) ? $this->priority : "--") . "</a>";
		}

		$section = array(
			'id' => 'info',
			'label' => $ARnls['ariadne:info'],
			'details' => yui::section_table($info)
		);

		echo yui::getSection($section);
	}
?>