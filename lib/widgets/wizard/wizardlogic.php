<?php
/********************************************************************
  Wizard logic, handles the wizard steps, prev/next and flow.

  + wgWizFlow : array
  wgWizFlow[0]["arCallArgs"]
  wgWizFlow[i]["title"]=string
  wgWizFlow[i]["template"]=string
  $wgWizFlow or $ARCurrent->wgWizFlow
  - wgWizNextStep : int, default 1
  - wgWizTitle : string, default "Wizard"
  - wgWizHeader
  - wgWizHeaderIcon
  - wgWizCallObject : object, default $this
  - wgWizCaption : string, default "", displayed below wgWizFlow

  No result.
 ********************************************************************/
if (!$wgWizCallObject) {
	if ($wgWizNewType) {
		if (!$wgWizNewfilename) {
			$wgWizNewFilename="{5:id}";
		}
		if (!$wgWizNewData) {
			$wgWizNewData=new baseObject;
		}
		$wgWizNewPath=$this->make_path($wgWizNewFilename);
		$wgWizCallObject=$this->store->newobject($wgWizNewPath, $this->path, $wgWizNewType, $wgWizNewData);
		$wgWizCallObject->arIsNewObject=true;
	} else {
		$wgWizCallObject=$this;
	}
}
if (!$wgWizTitle) {
	$wgWizTitle="Wizard"; //FIXME: nls value?
}
if (!$wgWizFlow || !is_array($wgWizFlow)) {
	$wgWizFlow=$ARCurrent->wgWizFlow;
	if (!$wgWizFlow || !is_array($wgWizFlow)) {
		error("wgWizFlow undefined or corrupt");
	}
}

$wgWizShowSections=false;
$tcount = 0;
if(isset($wgWizFlow) && is_array($wgWizFlow)){
	foreach( $wgWizFlow as $wizStep ) {
		if( $wizStep["template"]) {
			$tcount++;
		}
	}
}
if( $tcount > 1 ) {
	$wgWizShowSections = true;
}
/*
	if ($wgWizFlow && (count($wgWizFlow)>1 )) {
	$wgWizShowSections=true;
	} else {
	$wgWizShowSections=false;
	}
 */
if (!$wgWizAction) {
	$wgWizAction=$this->getdata("wgWizAction","none");
}
if (!$wgWizControl) {
	$wgWizControl=$this->getdata("wgWizControl","none");
}
if (!$wgWizCurrent) {
	$wgWizCurrent=$this->getdata("wgWizCurrent","none");
}
if (!$wgWizNextStep && !($wgWizNextStep=$ARCurrent->wgWizNextStep)) {
	$wgWizNextStep=$this->getdata("wgWizNextStep","none");
}
if( !$wgWizNextStep) {
	$wgWizNextStep = 1;
}

if( $wgWizFlow[0][$wgWizAction] ) {
	$wgWizTemplate = $wgWizFlow[0][$wgWizAction];
} else if ($wgWizShowSections) {
	$wgWizTemplate=$wgWizFlow[$wgWizNextStep]["template"];
} else if ($wgWizFlow[1]["template"]) { // no showsections, check if step 1 has a template
	$wgWizTemplate=$wgWizFlow[1]["template"];
} else {
	$wgWizTemplate=$wgWizFlow[0]["template"];
}
if (!$wgWizHeader) {
	$wgWizHeader="&nbsp;";
}
/* Default to no tabs, only used in new and edit wizard, while there are way more other dialogs
	if (!$wgWizTabsTemplate && $wgWizTabsTemplate!==false && !$wgWizFlow[$wgWizNextStep]["nolang"]) {
	$wgWizTabsTemplate='dialog.edit.languagetabs.php';
	}
 */
if ($wgWizFlow[$wgWizNextStep]["nolang"] && $wgWizTabsTemplate == 'dialog.edit.languagetabs.php') {
	unset($wgWizTabsTemplate);
}
