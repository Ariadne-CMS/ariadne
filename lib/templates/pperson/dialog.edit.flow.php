<?php
	$ARCurrent->nolangcheck=true;
	if (($this->CheckLogin("edit") || $this->CheckLogin("add", ARANYTYPE)) && $this->CheckConfig()) {
		
		foreach( $wgWizFlow as $key => $flow ) {
			if( $flow["template"] == "dialog.edit.form.php" ) {
				$wgWizFlow[$key]["title"] = $ARnls["name"];
				break;
			}
		}
		$wgWizFlow[] = array(
			"title" => $ARnls["address"],
			"image" => $AR->dir->images."wizard/address.png",
			"template" => "dialog.edit.address.php",
			"nolang" => true,
		);
		
		$wgWizFlow[] = array(
			"title" => $ARnls["contactinformation"],
			"image" => $AR->dir->images."wizard/contact.png",
			"template" => "dialog.edit.contact.php",
			"nolang" => true,
		);
		
		$arResult = $wgWizFlow;
	}
?>