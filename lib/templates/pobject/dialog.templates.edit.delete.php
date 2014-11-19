<?php

	if( $this->CheckLogin("layout") && $this->CheckConfig() ) {
		$this->call("system.delete.template.php", Array(
			"type"		=> $this->getvar("newtype"),
			"function"	=> $this->getvar("newfunction"),
			"language"	=> $this->getvar("newlanguage"),
			"default"	=> $this->getvar("default"),
			"template"	=> $this->getvar("template")
		));
		if( !$this->error) {
			$this->call("dialog.templates.edit.cancel.php"); // this will sent the user to the overview
		} else {
			$arCallArgs["error"] = $this->error;
			$this->call("dialog.templates.edit.form.php");
		}
	}

?>
