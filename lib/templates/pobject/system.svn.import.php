<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$repository = $this->getdata('repository');
		$username = $this->getdata('username');
		$password = $this->getdata('password');
		$message = $this->getdata('message');

		if (isset($repository)) {
			$repository = rtrim($repository, "/") . "/";
			$fstore	= $this->store->get_filestore_svn("templates");
			$svn	= $fstore->connect($this->id, $username, $password);

			if ($svn['info']['Revision']) {
				echo $this->path . " is already under version control - update instead.<br>";
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

			echo "<span class='svn_headerline'>Importing ".$this->path." in ".$repository."</span>\n\n";

			$mkdirs = $fstore->svn_mkdirs($svn, $repository);
			if (!$mkdirs) {
				echo "Repository already exists - use checkout and add instead\n";
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

			echo "<span class='svn_adddirline'>Adding ".$this->path."</span>\n";
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

							$fileinfo[$pinp_filename] = array();
							$fileinfo[$pinp_filename]['ar:function'] = $function;
							$fileinfo[$pinp_filename]['ar:type'] = $type;
							$fileinfo[$pinp_filename]['ar:language'] = $language;
							$fileinfo[$pinp_filename]['ar:default'] = $default;
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
				if( substr($line, 0, 12) == "Transmitting" ) {
					echo "<span class='svn_adddirline'>".$line."</span>\n";
				}
                 if( substr($line, 0, 9) == "Committed" ) {
					echo "<span class='svn_revisionline'>".$line."</span>\n";
				}
			}
			echo "\n";
			flush();
		}
	}
?>