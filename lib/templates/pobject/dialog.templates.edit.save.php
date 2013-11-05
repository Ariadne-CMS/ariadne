<?php
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);      
		exit;
	}

	if( $this->CheckLogin("layout") && $this->CheckConfig() ) {
		$args = array(
			"type"		=> $this->getvar("newtype"),
			"function"	=> $this->getvar("newfunction"),
			"language"	=> $this->getvar("newlanguage"),
			"default"	=> $this->getvar("default"),
			"template"	=> $this->getvar("template"),
			"private" 	=> $this->getvar("private")
		);
		if ( ($args['function']=='config.ini') && ($this instanceof $args['type'])) {
			$result = $this->call('system.template.test.phtml', $args );
			if (ar_error::isError($result)) {
				$this->error = $result->getMessage();
			}
		}
		if ( !$this->error ) {
			$this->call("system.save.layout.phtml", $args );
		}
		if ( !$this->error ) {
			$this->call("window.opener.objectadded.js");
			$this->call("dialog.templates.edit.cancel.php"); // this will sent the user to the overview
		} else {
			$this->call("dialog.templates.edit.form.php", array("error" => $this->error));
		}
	}
	
?>