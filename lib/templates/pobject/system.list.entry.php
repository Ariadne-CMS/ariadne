<?php
	$arResult = array(
		"name" => $this->nlsdata->name,
		"filename" => basename($this->path),
		"path" => $this->path,
		"parent" => $this->parent,
		"type" => $this->type,
		"size" => $this->size,
		"owner" => $this->data->config->owner,
		"lastchanged" => $this->lastchanged,
		"language" => $this->data->nls->list,
		'local_url' => $this->make_ariadne_url(),
		'priority' => $this->priority,
		"vtype" => $this->vtype,
		"icons" => array(
			"small" => ( $ARCurrent->arTypeIcons[$this->type]["small"] ? $ARCurrent->arTypeIcons[$this->type]["small"] : $this->call('system.get.icon.php', array('size' => 'small')) ),
			"medium" => ( $ARCurrent->arTypeIcons[$this->type]["medium"] ? $ARCurrent->arTypeIcons[$this->type]["medium"] : $this->call('system.get.icon.php', array('size' => 'medium')) ),
			"large" => ( $ARCurrent->arTypeIcons[$this->type]["large"] ? $ARCurrent->arTypeIcons[$this->type]["large"] : $this->call('system.get.icon.php', array('size' => 'large')) )
		)
	);
	
	if ($AR->user && !$this->CheckSilent("read")) {
		$arResult['grants']['read'] = false;
	}

	if( $this->implements("pshortcut") ) {
		$arResult["overlay_icon"] = $arResult["icons"]["small"];
		$arResult["overlay_icons"] = $arResult["icons"];

		$arResult["icons"] = array(
			"small" => ( $ARCurrent->arTypeIcons[$this->vtype]["small"] ? $ARCurrent->arTypeIcons[$this->vtype]["small"] : $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'small')) ),
			"medium" => ( $ARCurrent->arTypeIcons[$this->vtype]["medium"] ? $ARCurrent->arTypeIcons[$this->vtype]["medium"] : $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'medium')) ),
			"large" => ( $ARCurrent->arTypeIcons[$this->vtype]["large"] ? $ARCurrent->arTypeIcons[$this->vtype]["large"] : $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'large')) )
		);
	} 
	
	$arResult["icon"]=$arResult["icons"]["small"];

	if ($AR->SVN->enabled) {
		$svn = array();

		$filestore = $this->store->get_filestore_svn("templates");
		$svn_object = $filestore->connect($this->id);
		$svn_status = $filestore->svn_status($svn_object);

		if ($svn_status) {
			foreach ($svn_status as $key => $value) {
				if (substr($key, -5) != ".pinp") {
					unset($svn_status[$key]);
				}
			} 
			if (count($svn_status)) {
				$svn['status'] = 'modified';
				$svn_icon = $AR->dir->images . 'svn/ModifiedIcon.png';
			} else {
				$svn['status'] = 'insubversion';
				$svn_icon = $AR->dir->images . 'svn/InSubVersionIcon.png';
			}
		} else {
			$svn['status'] = 'notinsubversion';
		}

		$arResult['svn'] = $svn;
		$arResult['svn_icon'] = $svn_icon;
	}
?>