<?php
	$ARCurrent->nolangcheck = true;

	$arNewType = $this->getdata("arNewType","none");
	if ( !$arNewType ) {
		error( $ARnls["err:nonewtype"] );
		$this->store->close();
		exit();
	}

	if ( $this->CheckLogin("add", $arNewType) && $this->CheckConfig() ) {

		$arNewFilename = $this->getdata("arNewFilename","none");
		$arNewData = new object();
		$arNewPath = $this->make_path($arNewFilename);
		$wgWizCallObject = $this->store->newobject($arNewPath, $this->path, $arNewType, $arNewData);
		$wgWizCallObject->arIsNewObject = true;

		$wgWizCallObject->call("dialog.edit.save.php");
	}
?>
