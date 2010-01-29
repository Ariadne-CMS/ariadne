<?php
	if ($AR->user && !$this->CheckSilent("read")) {
		$arResult['grants']['read'] = false;
	} else {
		$grants=$this->GetValidGrants();			
	}

	$filename = $this->path;
	$filename = substr($filename, strlen($this->parent));
	$filename = substr($filename, 0, strlen($filename) - 1);

	$arResult["name"]=$this->nlsdata->name;
	$arResult["filename"]=$filename;
	$arResult["path"]=$this->path;
	$arResult["parent"]=$this->parent;
	$arResult["type"]=$this->type;
	$arResult["size"]=$this->size;
	$arResult["owner"]=$this->data->config->owner;
	$arResult["lastchanged"]=$this->lastchanged;
	$arResult["language"]=$this->data->nls->list;
	$arResult['local_url'] = $this->make_local_url();
	$arResult['priority'] = $this->priority;
	$arResult["vtype"]=$this->vtype;

	$arResult["icons"]["small"] = $this->call('system.get.icon.php', array('size' => 'small'));
	$arResult["icons"]["medium"] = $this->call('system.get.icon.php', array('size' => 'medium'));
	$arResult["icons"]["large"] = $this->call('system.get.icon.php', array('size' => 'large'));

	if( $this->implements("pshortcut") ) {
		$arResult["overlay_icon"] = $arResult["icons"]["small"];
		$arResult["overlay_icons"]["small"] = $arResult["icons"]["small"];
		$arResult["overlay_icons"]["medium"] = $arResult["icons"]["medium"];
		$arResult["overlay_icons"]["large"] = $arResult["icons"]["large"];
		$arResult["icons"]["small"] = $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'small'));
		$arResult["icons"]["medium"] = $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'medium'));
		$arResult["icons"]["large"] = $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'large'));
	}
	
	$arResult["icon"]=$arResult["icons"]["small"];

//	$arResult["pre"]=$flag;

	if ($AR->SVN->enabled) {
		$svn = array();

		$filestore = $this->store->get_filestore_svn("templates");
		$svnstack = &PEAR_ErrorStack::singleton('VersionControl_SVN');
		$svn_object = $filestore->connect($this->id);

		$svn_info = $filestore->svn_info($svn_object);
		ob_start();
		$svn_status = $filestore->svn_status($svn_object);
		ob_end_clean();

		if ($svn_status) {
			foreach ($svn_status as $key => $value) {
				if (substr($key, -5) == ".pinp") {
				} else {
					unset($svn_status[$key]);
				}
			} 
			if (sizeof($svn_status)) {
				$svn['status'] = 'modified';
				$svn_icon = $AR->dir->images . 'svn/ModifiedIcon.png';
			} else {
				$svn['status'] = 'insubversion';
				$svn_icon = $AR->dir->images . 'svn/InSubVersionIcon.png';
			}
		} else if ($svn_info) {
			$svn['status'] = 'insubversion';
			$svn_icon = $AR->dir->images . 'svn/InSubVersionIcon.png';

		} else {
			$svn['status'] = 'notinsubversion';
		}

		$arResult['svn'] = $svn;
		$arResult['svn_icon'] = $svn_icon;
	}

?>