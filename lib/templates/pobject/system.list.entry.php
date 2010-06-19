<?php
	if ($AR->user && !$this->CheckSilent("read")) {
		$arResult['grants']['read'] = false;
	}

	$arResult["name"]=$this->nlsdata->name;
	$arResult["filename"]=basename($this->path);
	$arResult["path"]=$this->path;
	$arResult["parent"]=$this->parent;
	$arResult["type"]=$this->type;
	$arResult["size"]=$this->size;
	$arResult["owner"]=$this->data->config->owner;
	$arResult["lastchanged"]=$this->lastchanged;
	$arResult["language"]=$this->data->nls->list;
	$arResult['local_url'] = $this->make_ariadne_url();
	$arResult['priority'] = $this->priority;
	$arResult["vtype"]=$this->vtype;

	

	$arResult["icons"]["small"] = ( $ARCurrent->arTypeIcons[$this->type]["small"] ? $ARCurrent->arTypeIcons[$this->type]["small"] : $this->call('system.get.icon.php', array('size' => 'small')) );
	$arResult["icons"]["medium"] = ( $ARCurrent->arTypeIcons[$this->type]["medium"] ? $ARCurrent->arTypeIcons[$this->type]["medium"] : $this->call('system.get.icon.php', array('size' => 'medium')) );
	$arResult["icons"]["large"] = ( $ARCurrent->arTypeIcons[$this->type]["large"] ? $ARCurrent->arTypeIcons[$this->type]["large"] : $this->call('system.get.icon.php', array('size' => 'large')) );

	if( $this->implements("pshortcut") ) {
		$arResult["overlay_icon"] = $arResult["icons"]["small"];
		$arResult["overlay_icons"]["small"] = $arResult["icons"]["small"];
		$arResult["overlay_icons"]["medium"] = $arResult["icons"]["medium"];
		$arResult["overlay_icons"]["large"] = $arResult["icons"]["large"];

		$arResult["icons"]["small"] = ( $ARCurrent->arTypeIcons[$this->vtype]["small"] ? $ARCurrent->arTypeIcons[$this->vtype]["small"] : $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'small')) );
		$arResult["icons"]["medium"] = ( $ARCurrent->arTypeIcons[$this->vtype]["medium"] ? $ARCurrent->arTypeIcons[$this->vtype]["medium"] : $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'medium')) );
		$arResult["icons"]["large"] = ( $ARCurrent->arTypeIcons[$this->vtype]["large"] ? $ARCurrent->arTypeIcons[$this->vtype]["large"] : $this->call('system.get.icon.php', array('type' => $this->vtype, 'size' => 'large')) );
	} 
	
	$arResult["icon"]=$arResult["icons"]["small"];

//	$arResult["pre"]=$flag;

	if ($AR->SVN->enabled) {
		$svn = array();

		$filestore = $this->store->get_filestore_svn("templates");
		$svnstack = &PEAR_ErrorStack::singleton('VersionControl_SVN');
		$svn_object = $filestore->connect($this->id);

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
		} else {
			$svn['status'] = 'notinsubversion';
		}

		$arResult['svn'] = $svn;
		$arResult['svn_icon'] = $svn_icon;
	}

?>