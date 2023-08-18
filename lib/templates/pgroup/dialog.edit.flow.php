<?php
$ARCurrent->nolangcheck=true;
if (($this->CheckLogin("edit") || $this->CheckLogin("add", ARANYTYPE)) && $this->CheckConfig()) {
	foreach( $wgWizFlow as $key => $flow ) {
		if( isset( $flow[ "template" ] ) &&  $flow["template"] == "dialog.edit.form.php" ) {
			$wgWizFlow[$key]["nolang"] = true;
		}
	}

	$arResult = $wgWizFlow;
}

?>
