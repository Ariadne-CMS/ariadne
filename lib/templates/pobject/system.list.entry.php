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

	// Sort the languages alphabetic
	if (is_array($arResult['language'])) {
		asort($arResult['language']);
	}

	if ($AR->user && !$this->CheckSilent("read")) {
		$arResult['grants']['read'] = false;
	}

	$arResult["icon"]=$arResult["icons"]["small"];

	if ($AR->SVN->enabled) {
		$svn = array();

		$filestore = $this->store->get_filestore_svn("templates");
		$svn_object = $filestore->connect($this->id);
		$svn_status = $filestore->svn_status($svn_object);

		if ($svn_status) {
			$svn['status'] = 'insubversion';
			$svn_icon = $AR->dir->images . 'svn/InSubVersionIcon.png';
			foreach ($svn_status as $key => $value) {
				if ( ( substr($key, -5) == ".pinp"  || $key === '/' ) && (
					$value['wc-status']['item']  != 'normal'  ||
					$value['wc-status']['props'] != 'normal' 
				) ){
					$svn['status'] = 'modified';
					$svn_icon = $AR->dir->images . 'svn/ModifiedIcon.png';
					break;
				}
			}
		} else {
			$svn['status'] = 'notinsubversion';
		}

		$arResult['svn'] = $svn;
		$arResult['svn_icon'] = $svn_icon;
	}
?>
