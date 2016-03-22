<?php
	$colDefs = ar::getvar('columns');

	$defaults = array(
		"name"        => $this->nlsdata->name,
		"filename"    => basename($this->path),
		"path"        => $this->path,
		"parent"      => $this->parent,
		"type"        => $this->type,
		"size"        => $this->size,
		"owner"       => $this->data->config->owner,
		"lastchanged" => date("Y-m-d H:i",$this->lastchanged),
		"modified"    => date("Y-m-d H:i",$this->data->mtime),
		"created"     => date("Y-m-d H:i",$this->data->ctime),
		"language"    => $this->data->nls->list,
		'local_url'   => $this->make_ariadne_url(),
		'priority'    => $this->priority,
		"vtype"       => $this->vtype,
		"icons"       => array(
			"small" => ( $ARCurrent->arTypeIcons[$this->type]["small"] ? $ARCurrent->arTypeIcons[$this->type]["small"] : $this->call('system.get.icon.php', array('size' => 'small')) ),
			"medium" => ( $ARCurrent->arTypeIcons[$this->type]["medium"] ? $ARCurrent->arTypeIcons[$this->type]["medium"] : $this->call('system.get.icon.php', array('size' => 'medium')) ),
			"large" => ( $ARCurrent->arTypeIcons[$this->type]["large"] ? $ARCurrent->arTypeIcons[$this->type]["large"] : $this->call('system.get.icon.php', array('size' => 'large')) )
		)
	);

	if (!$colDefs) {
		$colDefs = $defaults;
	}

	$arResult = [];
	//FIXME: needs refactoring, or \arc\hash::get() must accept '/' as 'return the root entry itself'
	foreach($colDefs as $key => $colDef ) {
		if ( array_key_exists($key, $defaults)) {
			$arResult[$key] = $defaults[$key];
		} else if ( $colDef['call'] ) {
			$arResult[$key] = ar::call($colDef['call'], $colDef);
		} else if ( $colDef['entry'] ) {
			$root = \arc\path::head($colDef['entry']);
			$tail = \arc\path::tail($colDef['entry']);
			switch( $root ) {
				case 'data':
					$first = \arc\path::head($tail);
					$tail  = \arc\path::tail($tail);
					if ( is_object($this->data->{$first}) ) {
						$item = \arc\path::head($tail);
						$tail = \arc\path::tail($tail);
						if ( $tail == '/' ) {
							$arResult[$key] = $this->data->{$first}->{$item};
						} else {
							$arResult[$key] = \arc\hash::get($this->data->{$first}->{$item}, $tail);
						}
					} else if ( $tail == '/' ) {
						$arResult[$key] = $this->data->{$first};
					} else {
						$arResult[$key] = \arc\hash::get($this->data->{$first}, $tail);
					}
				break;
				case 'nlsdata':
					$first = \arc\path::head($tail);
					$tail  = \arc\path::tail($tail);
					if ( $tail == '/' ) {
						$arResult[$key] = $this->nlsdata;
					} else {
						$arResult[$key] = \arc\hash::get($this->nlsdata, $tail);
					}
				break;
				case 'custom':
					if ( $tail == '/' ) {
						$arResult[$key] = $this->data->custom;
					} else {
						$arResult[$key] = \arc\hash::get($this->data->custom, $tail);
					}
				break;
				default:
					$arResult[$key] = null;
				break;
			}
		}
	}

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
		$svn_status = $this->getdatacache(sprintf("svn-status-%s",$svn_object));

		if ( !(isset($svn_status) && is_array($svn_status)) ) {
			$svn_status = $filestore->svn_status($svn_object);
			if (isset($svn_status) && is_array($svn_status)) {
				$this->savedatacache(sprintf("svn-status-%s",$svn_object), $svn_status, 999);
			}
		}

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