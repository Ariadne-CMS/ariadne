<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$workspace = $this->getdata('workspacepath');

		if (is_array($workspace)) {
			$this->store->revertLayer($this->path, array_keys($workspace));
		}
		$this->call("window.close.objectadded.js");
	}
?>
