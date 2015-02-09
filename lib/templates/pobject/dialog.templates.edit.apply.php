<?php

	if( $this->CheckLogin("layout") && $this->CheckConfig() ) {
		$args = array(
			"type"		=> $this->getvar("newtype"),
			"function"	=> $this->getvar("newfunction"),
			"language"	=> $this->getvar("newlanguage"),
			"default"	=> $this->getvar("default"),
			"template"	=> $this->getvar("template"),
			"private"   => $this->getvar("private")
		);
		if ( ($args['function']=='config.ini') && ($this instanceof $args['type'])) {
			$result = $this->call('system.template.test.phtml', $args );
			if (!$result) {
				$this->error = 'Unknown error in template '.$args['function'];
			} else if (ar_error::isError($result)) {
				$this->error = $result->getMessage();
			}
		}
		if ( !$this->error ) {
			$this->call("system.save.layout.phtml", $args );
		}
		if ( !$this->error ) {
			$this->call("window.opener.objectadded.js");
			$formArgs = array();
		} else {
			$formArgs = array( 'error' => $this->error );
		}
		$this->call( "dialog.templates.edit.form.php", $formArgs );
	}
	
?>
