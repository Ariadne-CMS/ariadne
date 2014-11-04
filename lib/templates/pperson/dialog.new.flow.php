<?php
	$ARCurrent->nolangcheck=true;
	// empty, do nothing for pobject.
	if ($this->CheckLogin("add") && $this->CheckConfig()) {
		$arResult = $wgWizFlow;

		// prefill intelligence, separate checks for nls name and normal name for compat with users etc
		$fname = $this->getdata("arNewFilename","none");
		if ($fname && !$this->getdata("lastname","none") ) {
			$_POST["lastname"] = $fname;
		}
	}
?>