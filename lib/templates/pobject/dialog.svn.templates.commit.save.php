<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		echo "<pre class='svn_result'>";
		set_time_limit(0);
		$this->call("system.svn.commit.php", $arCallArgs);
		echo "</pre>";
		$this->call("window.opener.objectadded.js");
	}
?>
