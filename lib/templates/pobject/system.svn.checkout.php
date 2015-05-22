<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		set_time_limit(0);
		$this->resetloopcheck();

		$repository = $this->getdata('repository');
		$username 	= $this->getdata('username');
		$password 	= $this->getdata('password');
		$checkunder = $this->getdata('checkunder');
		$revision	= $this->getdata('revision');

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
				echo $this->path . " is already under version control - update instead.\n";
			} else {

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

				$task = "Checking out";
				if( $checkunder ) {
					$task = "Checking under";
				}
				if( !$repoPath ) { // echo on the first run
					echo "<span class='svn_headerline'>".$task." ".$repository." on ".$this->path."</span>\n\n";
					flush();
				}

				if( $checkunder ) {
					$result = $fstore->svn_checkunder($svn, $repository, $revision);
				} else {
				// Checkout the templates.
					$result = $fstore->svn_checkout($svn, $repository, $revision);
				}
				$last = array_pop($result);

				if ($result) {
					$templates = array();
					foreach ($result as $item) {
						$templates[] = $item['name'];
						if( $item["filestate"]  == "A" ) {
							$props = $fstore->svn_get_ariadne_props($svn, $item['name']);
							echo "<span class='svn_addtemplateline'>Adding ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "").( $props["ar:private"] == '1' ? " " . $ARnls["ariadne:template:private"] : "") . "</span>\n";
						}
						flush();
					}
					echo "<span class='svn_revisionline'>Done ".$this->path." Revision ".$last["revision"]."</span>\n\n";

					$res = $this->call(
						"system.svn.compile.templates.php",
						array(
							'templates' => $templates,
							'fstore'    => $fstore,
							'svn'       => $svn
						)
					);
				} else {
					echo "<span class='svn_error'>Error: " . $svn['instance']->add->_stack->_errors[0]['params']['errstr'] . "</span>\n\n";
				}
			}
		}
	}
?>
