<?php
	$ARCurrent->nolangcheck=true;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
	$this->call("system.save.preferences.phtml");
	if (!$this->error) {
		$this->call("window.close.js");
	} else {
		echo "<font color='red'>$this->error</font>";
	}
?>
