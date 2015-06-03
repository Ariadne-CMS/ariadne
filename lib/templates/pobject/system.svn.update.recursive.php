<?php
	$ARCurrent->nolangcheck = true;

	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		set_time_limit(0);
		$this->resetloopcheck();

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id, $this->getdata("username"), $this->getdata("password"));
		$svn_info = $fstore->svn_info($svn);
		$stored_repository = rtrim($svn_info['url'], "/") . "/";
		$revision = $this->getdata('revision');
		$repository = $this->getdata('repository');

		$repoPath = $this->getdata("repoPath");

		if ($repoPath) {
			$repo_subpath = substr($this->path, strlen($repoPath));
			$repository = rtrim($repository, "/") . "/" . $repo_subpath;
		} else {
			$repository = $stored_repository;
		}

		if (!$svn_info) {
			echo "\n<span class='svn_error'>" . $this->path . ": is not in SVN.</span>\n";
			flush();
		} else if (($repository != $stored_repository) && $revision) {
			echo "Checked repo: [$repository] [$stored_repository] rev $revision";
			echo "\n<span class='svn_error'>" . $this->path . ": " . $ARnls['err:svn:leaving_recurse_tree'] . "</span>\n";
			flush();
		} else {
			if(!$revision) {
				echo "\n<span class='svn_headerline'>Updating ".$this->path." from ".$repository."</span>\n";
			} else {
				echo "\n<span class='svn_headerline'>Updating ".$this->path." to revision $revision from $repository</span>\n";
			}
			flush();

			// Update the templates.
			$result = $fstore->svn_update($svn, '', $revision);

			if ($result) {
				$revisionentry = array_pop($result);
				$updated_templates = array();
				$deleted_templates = array();

				foreach ($result as $item) {
					switch ($item['filestate']) {
						case "A":
						case "U":
						case "M":
						case "G":
							$updated_templates[] = $item['name'];
							break;
						case "D":
							$deleted_templates[] = $item['name'];
							break;
						case "Skipped":
						case "C":
							break; // Don't try to recompile conflicted templates
						default:
							$updated_templates[] = $item['name'];
							break;
					}

					$props = $fstore->svn_get_ariadne_props($svn, $item['name']);
					if( $item["filestate"]  == "A" ) {
						echo "<span class='svn_addtemplateline'>Added ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["filestate"] == "U" ) { // substr to work around bugs in SVN.php
						echo "<span class='svn_revisionline'>Updated ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["filestate"] == "M" || $item["filestate"] == "G" ) {
						echo "<span class='svn_revisionline'>Merged ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["filestate"] == "C" ) {
						echo "<span class='svn_revisionline'>Conflict ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["filestate"] == "D" ) {
						echo "<span class='svn_deletetemplateline'>Deleted ".$item["name"]."</span>\n"; // we don't know the props since it's deleted.
					} else {
						echo $item["filestate"]." ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."\n";
					}
					flush();
				}
				//FIXME: add revision/rest output line

				$this->call(
					"system.svn.compile.templates.php",
					array(
						'templates' => $updated_templates,
						'fstore'    => $fstore,
						'svn'       => $svn
					)

				);

				$this->call(
					"system.svn.delete.templates.php",
					array(
						'templates' => $deleted_templates,
						'fstore'    => $fstore,
						'svn'       => $svn
					)
				);

			}

			// Run update on the existing subdirs.
			$arCallArgs['repoPath'] = $this->path;
			$arCallArgs['repository'] = $repository;
			$arCallArgs['revision'] = $revision;

			$this->ls($this->path, "system.svn.update.recursive.php", $arCallArgs);

			// Create the dirs, restore them if needed.
			$dirlist = $fstore->svn_list($svn, $revision);
			if ($dirlist) {
				$arCallArgs['dirlist'] = $dirlist;
				$arCallArgs['svn'] = $svn;
				$arCallArgs['fstore'] = $fstore;
				$arCallArgs['repository'] = $repository;
				$this->call("system.svn.checkout.dirs.php", $arCallArgs);
			}
			flush();
		}
	}
?>
