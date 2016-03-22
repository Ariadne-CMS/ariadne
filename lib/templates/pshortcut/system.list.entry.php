<?php
	$arResult = $this->call("pobject::system.list.entry.php", $arCallArgs);
	
	$arResult["overlay_icon"]  = $arResult["icons"]["small"];
	$arResult["overlay_icons"] = $arResult["icons"];

	$arResult["icons"] = array(
		"small" => ( $ARCurrent->arTypeIcons[$this->vtype]["small"] ? $ARCurrent->arTypeIcons[$this->vtype]["small"] : $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'small')) ),
		"medium" => ( $ARCurrent->arTypeIcons[$this->vtype]["medium"] ? $ARCurrent->arTypeIcons[$this->vtype]["medium"] : $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'medium')) ),
		"large" => ( $ARCurrent->arTypeIcons[$this->vtype]["large"] ? $ARCurrent->arTypeIcons[$this->vtype]["large"] : $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'large')) )
	);

	$arResult["icon"]=$arResult["icons"]["small"];
?>
