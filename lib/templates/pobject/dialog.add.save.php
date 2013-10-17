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
		$container = $this->getdata("location") ? $this->getdata("location") : $this->path;
		$arNewPath = $this->make_path($container . $arNewFilename);
		$wgWizCallObject = $this->store->newobject($arNewPath, $container, $arNewType, $arNewData);
		$wgWizCallObject->arIsNewObject = true;

		$wgWizCallObject->call("dialog.edit.save.php");
	}
?>
