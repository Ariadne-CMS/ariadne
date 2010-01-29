<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckLogin('read') && $this->CheckConfig()) {
		if (!$type) {
			$type = $this->type;
		}
		if (!$ARCurrent->arTypeIcons[$this->type]) {
			// FIXME: for performance the check above is necessary, but
			// it does make it possible to get the wrong icon...
			
			// get typetree to get the correct icon
			$this->call('typetree.ini');
		}
		if( !$size ) {
			$size = 'default';
		}
		if( $size == 'large' ) {
			$size = 'default';
		}
		if ($size == 'default') {
			$realsize = 'large';
		} else {
			$realsize = $size;
		}

		$icon=$ARCurrent->arTypeIcons[$type][$size];
		if (!$icon) {
			$dotPos=strpos($type, '.');
			if (false!==$dotPos) {
				$realtype=substr($type, 0, $dotPos);
				$icon=$ARCurrent->arTypeIcons[$realtype][$size];
			} else {
				$icon=$AR->dir->images."icons/".$realsize."/unknown.png";
			}
		}
		$arResult=$icon;
	}
?>