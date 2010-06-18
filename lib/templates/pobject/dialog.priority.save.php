<?php
	$ARCurrent->nolangcheck=true;
	if( $this->CheckLogin("edit") && $this->CheckConfig() ) {
		$this->call("system.save.priority.phtml", $arCallArgs);
		if (!$this->error) {
			$this->call("window.close.objectadded.js");
		} else {
			echo "<font color='red'>$this->error</font>";
		}
	}
?>