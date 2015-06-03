<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$repository = $this->getdata('repository');
		$username = $this->getdata('username');
		$password = $this->getdata('password');
		$message = $this->getdata('message');

		if (!isset($repository) || $repository == '') {
			echo $ARnls['err:svn:enterURL'];
			flush();
			return;
		} else {
			$repository = rtrim($repository, "/") . "/";
			$fstore	= $this->store->get_filestore_svn("templates");
			$svn	= $fstore->connect($this->id, $username, $password);
			$svn_info = $fstore->svn_info($svn);

			if ($svn_info['revision']) {
				echo $this->path . " is already under version control - update instead.<br>"; // FIXME: nls
			}

			if ($repoPath) {
				$repo_subpath = substr($this->path, strlen($repoPath));
			} else {
				// This is also the first loop!
				ob_start(); // FIXME: the SVN library is being a cunt and echoing when it shouldn't. So we catch it and destroy it.
				$fstore->svn_accept_cert($svn, $repository);
				ob_end_clean();
				$repo_subpath = '';
			}
			$repository = rtrim($repository, "/") . "/" . $repo_subpath;

			echo "<span class='svn_headerline'>Importing ".$this->path." in ".$repository."</span>\n\n"; // FIXME: nls

			$mkdirs = $fstore->svn_mkdirs($svn, $repository);
			if (!$mkdirs) {
				echo "Repository already exists - use checkout and add instead\n"; // FIXME: nls
				flush();
				return;
			}

			ob_start();
			$fstore->svn_checkout($svn, $repository);
			ob_end_clean();

			$fileinfo = array();
			$fileinfo[''] = array();
			$fileinfo['']['ar:type'] = $this->type;
			$fileinfo['']['ar:name'] = $this->nlsdata->name;

			echo "<span class='svn_adddirline'>Adding ".$this->path."</span>\n"; // FIXME: nls
			$pinp = $this->data->config->pinp;
			if ($pinp) {
				foreach( $pinp as $type => $values ) {
					foreach( $values as $function => $templatelist ) {
						foreach($templatelist as $language => $node) {
							$compiled_filename = $type . "." . $function . "." . $language;

							$pinp_filename = $type . "." . $function . "." . $language . ".pinp";
							if ($this->data->config->templates[$type][$function]) {
								$default = 1;
							} else {
								$default = 0;
							}

							if ($this->data->config->privatetemplates[$type][$function]) {
								$private = 1;
							} else {
								$private = 0;
							}

							$fileinfo[$pinp_filename] = array();
							$fileinfo[$pinp_filename]['ar:function'] = $function;
							$fileinfo[$pinp_filename]['ar:type'] = $type;
							$fileinfo[$pinp_filename]['ar:language'] = $language;
							$fileinfo[$pinp_filename]['ar:default'] = $default;
							$fileinfo[$pinp_filename]['ar:private'] = $private;
							echo "<span class='svn_addtemplateline'>Adding ".$this->path.$function." (".$type.") [".$language."] ".( $default == '1' ? $ARnls["default"] : "")."</span>\n";
							flush();
							$fstore->svn_add($svn, $pinp_filename);
						}
					}
				}
			}

			$result = $fstore->svn_commit($svn, $message, $fileinfo);
			$res = explode("\n", $result);
			foreach( $res as $line ) {
				if( substr($line, 0, 12) == "Transmitting" ) { // FIXME: nls
					echo "<span class='svn_adddirline'>".$line."</span>\n";
				}
				if( substr($line, 0, 9) == "Committed" ) { // FIXME: nls
					echo "<span class='svn_revisionline'>".$line."</span>\n";
				}
			}
			echo "\n";
			flush();
			// Run checkout on the existing subdirs.
			$arCallArgs['repoPath'] = $this->path;
			$arCallArgs['repository'] = $repository;

			$this->ls($this->path, "system.svn.import.recursive.php", $arCallArgs);
			flush();
		}
	}
?>
