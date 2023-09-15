<?php
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id, $repository ?? null, $username ?? null, $password ?? null);

		$type = $this->getvar("type");
		$function = $this->getvar("function");
		$language = $this->getvar("language");

		if ($type && $function && $language) {
			$filename = $type . "." . $function . "." . $language . ".pinp";

			$result = $fstore->svn_delete($svn, $filename);

			if ($result === false) {
				echo "SVN delete failed.<br>";
				if (count($errs = $fstore->svnstack->getErrors())) {
					foreach ($errs as $err) {
						echo $err['message']."<br>\n";
					}
				}
			} else {
				echo(htmlspecialchars($result));
			}
			flush();
		}
	}
?>
