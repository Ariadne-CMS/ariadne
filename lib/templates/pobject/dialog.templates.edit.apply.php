<?php

	if( $this->CheckLogin("layout") && $this->CheckConfig() ) {
	
		$this->call("system.save.layout.phtml", array(
			"type"		=> $this->getvar("newtype"),
			"function"	=> $this->getvar("newfunction"),
			"language"	=> $this->getvar("newlanguage"),
			"default"	=> $this->getvar("default"),
			"template"	=> $this->getvar("template"))
		);
		$this->call("window.opener.objectadded.js");	

		if( $this->error ) {
			$arCallArgs["error"] = $this->error;
		}
		$this->call("dialog.templates.edit.form.php", $arCallArgs); // back to edit mode
	}
	
?>