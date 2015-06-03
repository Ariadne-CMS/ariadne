<?php
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id, $username, $password);
		$svn_info = $fstore->svn_info($svn);

		if ($svn_info['revision']) {
			echo "Removing Version Control: ".$this->path."\n";
			$status = $fstore->svn_unsvn($svn);
		}
	}
?>
