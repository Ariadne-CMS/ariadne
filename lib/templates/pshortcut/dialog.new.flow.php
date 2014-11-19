<?php
	$ARCurrent->nolangcheck=true;
	// empty, do nothing for pobject.
	if ($this->CheckLogin("add") && $this->CheckConfig()) {

		$wgWizFlow = array();
		$wgWizFlow[] = array(
			"current" => $this->getdata("wgWizCurrent","none"),
			"cancel" => "dialog.edit.cancel.php",
			"save" => "dialog.edit.shortcut.save.php"
		);
		// inject filename step
		$wgWizFlow[] = array(
			"title" => $ARnls["filename"],
			"image" => $AR->dir->images.'wizard/info.png',
			"template" => "dialog.new.filename.php",
			"nolang" => true
		);

		$wgWizFlow[] = array(
			"title" => $ARnls["data"],
			"image" => $AR->dir->images.'wizard/data.png',
			"template" => "dialog.edit.shortcut.form.php"
		);

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
