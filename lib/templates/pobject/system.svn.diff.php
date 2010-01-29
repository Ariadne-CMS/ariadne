<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id, $repository, $username, $password);
		$type 		= $this->getvar("type");
		$function 	= $this->getvar("function");
		$language 	= $this->getvar("language");

		if ($type && $function && $language) {
			$filename = $type . "." . $function . "." . $language . ".pinp";
		}

		$status = $fstore->svn_diff($svn, $filename);

		if( $colorize ) {
			$status = $this->call("system.svn.diff.colorize.php", array("diff" => $status, "nowrap" => $nowrap));
		}

		$arResult = $status;
	}
?>