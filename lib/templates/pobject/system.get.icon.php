<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckLogin('read')) {
		if (!isset($type)) {
			$type = $this->type;
		}
		if (!isset($ARCurrent->arTypeIcons[$this->type])) {
			// FIXME: for performance the check above is necessary, but
			// it does make it possible to get the wrong icon...

			// get typetree to get the correct icon
			$this->call('typetree.ini');
		}
		if (!isset($size) || !$size || $size == "large") {
			$size = "default";
		}

		$icon=(isset($ARCurrent->arTypeIcons[$type][$size]) ? $ARCurrent->arTypeIcons[$type][$size] : null);
		if (!$icon) {
			$dotPos=strpos($type, '.');
			if (false!==$dotPos) {
				$realtype=substr($type, 0, $dotPos);
				$ARCurrent->arTypeIcons[$type] = $ARCurrent->arTypeIcons[$realtype];
				$icon=$ARCurrent->arTypeIcons[$realtype][$size];
			} else {
				$realsize = ($size == "default") ? "large" : $size;
				$icon=$AR->dir->images."icons/".$realsize."/unknown.png";
				$ARCurrent->arTypeIcons[$type][$size] = $icon;
			}
		}
		$arResult=$icon;
	}
?>
