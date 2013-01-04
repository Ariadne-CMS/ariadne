<?php

$ARCurrent->nolangcheck=true;
if (($this->CheckLogin("edit") || $this->CheckLogin("add", ARANYTYPE)) && $this->CheckConfig()) {

	foreach( $wgWizFlow as $key => $flow ) {
		if( $flow["template"] == "dialog.edit.form.php" ) {
			$wgWizFlow[$key] = array( //overwrite this index which contains form. form comes last.
				"title" => $ARnls["scenario"],
				"image" => $AR->dir->images."wizard/scenario.png",
				"template" => "dialog.edit.scenario.php",
				"nolang" => true,
			);
			break;
		}
	}

	$wgWizFlow[] = array(
		"title" => $ARnls["date"]."&nbsp;/&nbsp;".$ARnls["time"],
		"image" => $AR->dir->images."wizard/schedule.png",
		"template" => "dialog.edit.datetime.php",
		"nolang" => true,
	);

	$wgWizFlow[] = array(
		"title" => $ARnls["summary"],
		"image" => $AR->dir->images."wizard/data.png",
		"template" => "dialog.edit.form.php",
	);
	
	$arResult = $wgWizFlow;
}

?>