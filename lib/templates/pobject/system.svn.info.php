<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id);
		$svn_info = $fstore->svn_info($svn);
		if( is_array($svn_info) && count($svn_info)) {
			// Working-copy revisions can differ after individual template commits.
			// Report the latest template commit separately from the directory info.
			$highest_template_revision = null;
			$svn_status = $fstore->svn_status($svn);
			if (is_array($svn_status)) {
				foreach ($svn_status as $filename => $status) {
					$revision = $status['wc-status']['commit']['revision'] ?? null;
					if (substr($filename, -5) === '.pinp' && is_numeric($revision)) {
						$highest_template_revision = max($highest_template_revision ?? 0, (int) $revision);
					}
				}
			}
			$svn_info['Highest template revision'] = $highest_template_revision ?? 'Unavailable';
			$svn_info['Ariadne Path'] = $this->path;
			$svn_info['Ariadne Name'] = $this->nlsdata->name;
			ksort($svn_info);
		}

		$arResult = $svn_info;
	}
?>
