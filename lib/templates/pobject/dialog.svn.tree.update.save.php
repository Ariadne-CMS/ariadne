<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		echo "<pre class='svnresult'>\n";
		$this->call("system.svn.update.recursive.php", $arCallArgs);
		echo "</pre>\n";
		$this->call("window.opener.objectadded.js");
	}
?>