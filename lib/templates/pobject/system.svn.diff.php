<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();
		$username = $this->getdata('username');
		$password = $this->getdata('password');

		$fstore   = $this->store->get_filestore_svn("templates");
		$svn      = $fstore->connect($this->id, $username, $password);
		$type     = $this->getvar("type");
		$function = $this->getvar("function");
		$language = $this->getvar("language");

		if ($type && $function && $language) {
			$filename = $type . "." . $function . "." . $language . ".pinp";
		}

		$revision = $this->getvar("revision") ? $this->getvar("revision") : "";
		$status = $fstore->svn_diff($svn, $filename, $revision);

		if (ar('error')->isError($status)){
			$arResult = $status;
		} else {
			if( $colorize ) {
				$status = $this->call("system.svn.diff.colorize.php", array("diff" => $status, "nowrap" => $nowrap));
			}

			$arResult = $status;
		}
	}
?>
