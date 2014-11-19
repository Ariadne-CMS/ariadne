<?php
	$ARCurrent->nolangcheck=true;
	// empty, do nothing for pobject.
	if ($this->CheckLogin("add") && $this->CheckConfig()) {
		$arResult = $wgWizFlow;

		// prefill intelligence, separate checks for nls name and normal name for compat with users etc
		$fname = $this->getdata("arNewFilename","none");
		if ($fname ) {
			if(!$this->getdata("name", $ARConfig->nls->default)) {
				$_POST[$ARConfig->nls->default]["name"] = $fname;
			} elseif( !$this->getdata("name","none") ) {
				$_POST["name"] = $fname;
			}
		}

	}
?>
