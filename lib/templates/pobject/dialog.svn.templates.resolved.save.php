<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		echo "<pre class='svnresult'>\n";
		set_time_limit(0);
		$this->call("system.svn.revolved.php", $arCallArgs);
		echo "</pre>\n";
		$this->call("window.opener.objectadded.js");
	}
?>