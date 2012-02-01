<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		print_r($workspace);

//		echo "<pre class='svnresult'>\n";
//		$this->call("system.svn.update.php", $arCallArgs);
//		echo "</pre>\n";
		$this->call("window.opener.objectadded.js");
	}
?>