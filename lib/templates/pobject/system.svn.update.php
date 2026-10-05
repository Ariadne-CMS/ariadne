<?php
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();
		$this->error = null;
		$updateErrors = array();

		$fstore   = $this->store->get_filestore_svn("templates");
		$svn      = $fstore->connect($this->id, $this->getdata("username"), $this->getdata("password"));

		$type     = $this->getvar("type");
		$function = $this->getvar("function");
		$language = $this->getvar("language");

		$filename = '';

		if ($type && $function && $language) {
			$filename = $type . "." . $function . "." . $language . ".pinp";
		}

		$svn_info   = $fstore->svn_info($svn);
		$repository = $svn_info['url'];
		$revision   = $this->getdata("revision");

		if (!isset($repository) || $repository == '') {
			$this->error = $ARnls['err:svn:enterURL'];
			echo $this->error;
			flush();
			return;
		} else {
			$repository = rtrim($repository, "/") . "/" . ( $repo_subpath ?? "" );

			$updating = $this->path;
			if( $filename ) {
				$updating .= $function . " {".$type.") [".$language."]";
			}

			if(!$revision) {
				echo "\n<span class='svn_headerline'>Updating ".$this->path." from ".$repository."</span>\n";
			} else {
				echo "\n<span class='svn_headerline'>Updating ".$this->path." to revision $revision from $repository</span>\n";
			}
			flush();

			$result = $fstore->svn_update($svn, $filename, $revision);
			if ($result === false || ar_error::isError($result)) {
				$updateErrors[] = "SVN update failed.";
				echo "SVN update failed.\n";
				echo $result."\n";
				if (is_object($result) && ($result->previous ?? null)) {
					echo $result->previous."\n";
				}
				if (count($errs = $fstore->svnstack->getErrors())) {
					foreach ($errs as $err) {
						$updateErrors[] = $err['message'];
						echo $err['message']."\n";
					}
				}
				$this->error = implode("\n", $updateErrors);
			} else if ($result) {
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
						case "E":
							// existing template, no need for recompile
							break;
						case "C":
							$updateErrors[] = "SVN conflict in ".$this->path.$item['name']."; the template was not compiled.";
							break; // Don't try to compile conflicted templates
						case "Skipped":
							$updateErrors[] = "SVN skipped ".$this->path.$item['name']."; the template was not compiled.";
							break;
						default:
							$updated_templates[] = $item['name'];
							break;
					}
					$props = $fstore->svn_get_ariadne_props($svn, $item['name']);
					if( ar_error::isError($props)) {
						$updateErrors[] = $props->getMessage();
						echo "<span>Error: ".$props->getMessage()."</span>";
					} else if( $item["filestate"]  == "A" ) {
						echo "<span class='svn_addtemplateline'>Added ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["filestate"] == "U" ) { // substr to work around bugs in SVN.php
						echo "<span class='svn_revisionline'>Updated ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["filestate"] == "M" || $item["filestate"] == "G" ) {
						echo "<span class='svn_revisionline'>Merged ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["filestate"] == "E" ) {
						echo "<span class='svn_revisionline'>Existing ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["filestate"] == "C" ) {
						echo "<span class='svn_error'>Conflict ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."; template not compiled</span>\n";
					} elseif( $item["filestate"] == "D" ) {
						// system.svn.delete.templates.php will report that this template has been deleted
						//echo "<span class='svn_deletetemplateline'>Deleting ".$item["name"]."</span>\n"; // we don't know the props since it's deleted.
					} else {
						echo ($item["filestate"]?$item["filestate"]:"Unchanged ")." ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."\n";
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
				if ($this->error) {
					$updateErrors[] = (string) $this->error;
				}

				$this->error = null;
				$this->call(
					"system.svn.delete.templates.php",
					array(
						'templates' => $deleted_templates,
						'fstore'    => $fstore,
						'svn'       => $svn
					)
				);
				if ($this->error) {
					$updateErrors[] = (string) $this->error;
				}

				$this->error = $updateErrors ? implode("\n", array_unique($updateErrors)) : null;
			}
		}
	}
?>
