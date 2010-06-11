<?php
$ARCurrent->nolangcheck=true;
if ($this->CheckLogin("edit") && $this->CheckConfig()) {
	foreach( $wgWizFlow as $key => $flow ) {
		if( $flow["template"] == "dialog.edit.form.php" ) {
			$wgWizFlow[$key]["nolang"] = true;
		}
	}
	
	$arResult = $wgWizFlow;
}

?>