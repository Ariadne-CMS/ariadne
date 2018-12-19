<?php
	$ARCurrent->nolangcheck = true;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}

	$arNewType = $this->getdata("arNewType","none");
	if ( !$arNewType ) {
		error( $ARnls["err:nonewtype"] );
		$this->store->close();
		exit();
	}

	$location = $this->getvar( 'location' );

	if ( $this->CheckConfig() ) {

		$arNewFilename = $this->getdata("arNewFilename","none");
		$arNewData = new baseObject();
		$container = $this->getdata("location") ? $this->getdata("location") : $this->path;
		$arNewPath = $this->make_path($container . $arNewFilename);
		$wgWizCallObject = $this->store->newobject($arNewPath, $container, $arNewType, $arNewData);
		$wgWizCallObject->arIsNewObject = true;

		$wgWizFlow = $wgWizCallObject->call("user.wizard.new.html", array( "wgWizFlow" => array() ) );
		if ($wgWizFlow[0]['save']) {
			$wgWizCallObject->call($wgWizFlow[0]['save']);
		}

		$wgWizCallObject->call("dialog.edit.save.php");
	}
?>
