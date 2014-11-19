<?php
	$ARCurrent->nolangcheck=true;
	if( $this->CheckLogin("add", ARANYTYPE) && $this->CheckConfig()) {
		$this->call("dialog.add.list.php", array("showall" => 1));
	}
?>
