<?php
$ARCurrent->nolangcheck=true;

if ($this->CheckLogin("edit") && $this->CheckConfig()) {
                                                                                            
	$wgWizFlow[] = array(
		"title" => $ARnls["thumbnail"],
		"image" => $AR->dir->images."wizard/data.png",
		"template" => "dialog.edit.thumb.php",
		"nolang"=> true,
	);
	
	$arResult = $wgWizFlow;
}

?>