<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id);

		if( is_array($svn['info']) ) {
			$svn['info']['Ariadne Path'] = $this->path;
			$svn['info']['Ariadne Name'] = $this->nlsdata->name;
			ksort($svn['info']);
		}
		
		$arResult = $svn['info'];
	}
?>