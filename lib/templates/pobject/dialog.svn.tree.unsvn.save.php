<?php
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
	ldDisablePostProcessing();
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		echo "<pre class='svn_result'>";
		set_time_limit(0);
		$this->find($this->path, '', "system.svn.unsvn.php", $arCallArgs, 0, 0);
		echo "</pre>";
		$this->call("window.opener.objectadded.js");
	}
?>
