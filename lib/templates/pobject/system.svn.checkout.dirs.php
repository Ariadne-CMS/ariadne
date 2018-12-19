<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();
		if ($dirlist) {
			foreach ($dirlist as $item) {
				if ($item['kind'] == "dir") {
					//echo "found dir: " . $item['name'] . "<br>";

					$dirinfo = array();
					$dirinfo['ar:path'] = basename($item['name']);
					// Check if the dir exists - if not, create the object in Ariadne.
					$dirpath = $this->path . $dirinfo['ar:path'] . "/";

					if (!$this->exists($dirpath)) {

						// Fetch type and name information from SVN.
						// If not found in SVN:
						// type defaults to psection
						// name defaults to pathname
						$svn_type = $fstore->svn_rpropget($svn, $repository, "ar:type", $dirinfo['ar:path'], $revision);
						if ($svn_type && !ar_error::isError($svn_type) && $svn_type['property']['text'] != "") {
							$svn_type = $svn_type['property']['text'];
							//echo "SVN type: [$svn_type]<br>";
							$dirinfo['ar:type'] = $svn_type;
						} else {
							//echo "SVN type not found, default to psection<br>";
							$dirinfo['ar:type'] = "psection";
						}
						$svn_name = $fstore->svn_rpropget($svn, $repository, "ar:name", $dirinfo['ar:path'], $revision);

						if ($svn_name && !ar_error::isError($svn_name) && $svn_name['property']['text'] != "") {
							$dirinfo['ar:name'] = $svn_name['property']['text'];
						} else {
							$dirinfo['ar:name'] = $dirinfo['ar:path'];
						}

						echo "<span class='svn_adddirline'>Adding ".$this->path.$dirinfo['ar:path']." (".$dirinfo['ar:type'].")</span>\n";

						// Create the new object in Ariadne.
						$newData = array();
						$newData['arNewFilename'] 	= $dirinfo['ar:path'];
						$newData['arNewType'] 		= $dirinfo['ar:type'];
						$newData[$ARConfig->nls->default]['name']	= $dirinfo['ar:name'];
						$this->call("system.new.phtml", $newData);

						if ($this->error) {
							echo "Error: " . $this->error . "\n";
						} else {
							$arCallArgs['repository'] = $repository;
							$arCallARgs['revision'] = $revision;
							$this->get($dirpath, "system.svn.checkout.recursive.php", $arCallArgs);
						}
					}
				}
			}
		}
	}
?>
