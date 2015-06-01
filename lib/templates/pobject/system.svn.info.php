<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id);
		$svn_info = $fstore->svn_info($svn);
		if( is_array($svn_info) && count($svn_info)) {
			$svn_info['Ariadne Path'] = $this->path;
			$svn_info['Ariadne Name'] = $this->nlsdata->name;
			ksort($svn_info);
		}

		$arResult = $svn_info;
	}
?>
