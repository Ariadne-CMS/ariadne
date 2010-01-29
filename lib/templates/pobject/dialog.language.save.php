<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->call("system.save.language.php", $arCallArgs);
		if (!$this->error) {
			$this->call("window.close.objectadded.js");
		} else {
			echo "<font color='red'>$this->error</font>";
		}
	}
?>