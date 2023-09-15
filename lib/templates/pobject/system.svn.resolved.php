<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id, $repository ?? null, $username ?? null, $password ?? null);

		$type = $this->getvar("type");
		$function = $this->getvar("function");
		$language = $this->getvar("language");

		if ($type && $function && $language) {
			$filename = $type . "." . $function . "." . $language . ".pinp";
			$status = $fstore->svn_resolved($svn, $filename);
			print_r($status);
		}
	}
?>
