<?php
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id, $username??null, $password??null);
		$svn_info = $fstore->svn_info($svn);

		if ($svn_info['revision']??null) {
			echo "Removing Version Control: ".$this->path."\n";
			$status = $fstore->svn_unsvn($svn);
		}
	}
?>
