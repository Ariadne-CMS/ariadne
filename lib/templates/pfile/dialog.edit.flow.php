<?php

$ARCurrent->nolangcheck=true;
if ($this->CheckLogin("edit") && $this->CheckConfig()) {

	$this->call("system.save.tempfile.phtml");
	if ($this->error) {
		$this->call("show.error.phtml");
		exit;
	}
	
	foreach ($AR->nls->list as $language => $language_name) {
		if (!$this->getdata("name", $language)) {
			if (($file=$this->getdata("file", $language)) && preg_match("|[^\/\\]*\$|", $file, $matches)) {
				$arFilename = preg_replace("|[^a-z0-9\./_-]|i", "_", $matches[0]);
				$_POST[$language]["name"] = $arFilename;
			}
		}
	}

	$arResult = $wgWizFlow;
}

?>