<?php

$ARCurrent->nolangcheck=true;
if (($this->CheckLogin("edit") || $this->CheckLogin("add", ARANYTYPE)) && $this->CheckConfig()) {
	$wgWizFlow[] = array (
		"title"		=> "Connection", //FIXME use NLS
		"template"	=> "edit.object.data.conninfo.phtml",
		"image"     => $AR->dir->images."wizard/data.png",
		"nolang"	=> true
	);

	$wgWizFlow[] = array (
		"title"		=> "General filter", //FIXME use NLS
		"template"	=> "edit.object.data.generalfilter.phtml",
		"image"     => $AR->dir->images."wizard/data.png",
		"nolang"	=> true
	);

	$wgWizFlow[] = array (
		"title"		=> "Search translation", //FIXME use NLS
		"template"	=> "edit.object.data.searchtranslation.phtml",
		"image"     => $AR->dir->images."wizard/data.png",
		"nolang"	=> true
	);

	$wgWizFlow[] = array (
		"title"		=> "Other paramaters", //FIXME use NLS
		"template"	=> "edit.object.data.otherparams.phtml",
		"image"     => $AR->dir->images."wizard/data.png",
		"nolang"	=> true
	);

	$wgWizFlow[] = array (
		"title"		=> "User mapping", //FIXME use NLS
		"template"	=> "edit.object.data.usermapping.phtml",
		"image"     => $AR->dir->images."wizard/data.png",
		"nolang"	=> true
	);

	$arResult = $wgWizFlow;
}

?>
