<?php
	$ARCurrent->nolangcheck=true;
	// empty, do nothing for pobject.
	if ($this->CheckLogin("add") && $this->CheckConfig()) {

		$arResult[0] = $wgWizFlow[0];
		$arResult[] = array(
			"title" => $ARnls["uploadfile"],
			"image" => $AR->dir->images."wizard/upload.png",
			"template" => "dialog.new.upload.php",
		);
		foreach( $wgWizFlow as $k => $flow ) {
			if( $k > 0 ) {
				$arResult[] = $flow;
			}
		}


		foreach ($AR->nls->list as $language => $language_name) {
			$arNewFilename = $this->getdata("arNewFilename","none");
			if (!$arNewFilename || !$this->getdata("name", $language)) {
				if (($file=$this->getdata("file", $language)) && preg_match("|[^\/\\\]*\$|", $file, $matches)) {
					$arFilename = preg_replace("|[^a-z0-9\./_-]|i", "-", $matches[0]);
					if (!$arNewFilename) {
						$_POST['arNewFilename'] = $arFilename;
						$this->path = $this->store->make_path($this->parent, $arFilename);
					}
					if (!$this->getdata("name", $language)) {
						$_POST[$language]["name"] = $arFilename;
					}
				}
			}
		}
	}
?>
