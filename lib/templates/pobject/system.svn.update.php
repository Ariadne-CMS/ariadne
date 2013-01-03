<?php
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id, $this->getdata("username"), $this->getdata("password"));

		$type		= $this->getvar("type");
		$function 	= $this->getvar("function");
		$language 	= $this->getvar("language");

		if ($type && $function && $language) {
			$filename = $type . "." . $function . "." . $language . ".pinp";
		}

		$svn_info = $fstore->svn_info($svn);
		$repository = $svn_info['URL'];

		if (!isset($repository) || $repository == '') {
			echo $ARnls['err:svn:enterURL'];
			flush();
			return;
		} else {
			$repository = rtrim($repository, "/") . "/" . $repo_subpath;
			
			$updating = $this->path;
			if( $filename ) {
				$updating .= $function . " {".$type.") [".$language."]";
			} 
			
			echo "\n<span class='svn_headerline'>Updating ".$updating." from ".$repository."</span>\n";
			flush();

			$result = $fstore->svn_update($svn, $filename, $this->getdata("revision"));
			if ($result) {
				$updated_templates = array();
				$deleted_templates = array();

				foreach ($result as $item) {
					switch ($item['status']) {
						case "A":
						case "U":
						case "M":
						case "G":
							$updated_templates[] = $item['name'];
							break;
						case "D":
							$deleted_templates[] = $item['name'];
							break;
						case "C":
							break; // Don't try to recompile conflicted templates
						default:
							$updated_templates[] = $item['name'];
							break;
					}

					$props = $fstore->svn_get_ariadne_props($svn, $item['name']);
					if( $item["status"]  == "A" ) {
						echo "<span class='svn_addtemplateline'>Added ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["status"] == "U" || substr(ltrim($item['name']),0,2) == 'U ' ) { // substr to work around bugs in SVN.php
						echo "<span class='svn_revisionline'>Updated ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["status"] == "M" || $item["status"] == "G" ) {
						echo "<span class='svn_revisionline'>Merged ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["status"] == "C" ) {
						echo "<span class='svn_revisionline'>Conflict ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					} elseif( $item["status"] == "D" ) {
						echo "<span class='svn_deletetemplateline'>Deleted ".$item["name"]."</span>\n"; // we don't know the props since it's deleted.
					} else {
						echo $item["status"]." ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."\n";
					}
					flush();
				}
	
				$this->call(
					"system.svn.compile.templates.php", 
					array(
						'templates' 	=> $updated_templates,
						'fstore'	=> $fstore,
						'svn'		=> $svn
					)
				);

				$this->call(
					"system.svn.delete.templates.php",
					array(
						'templates'	=> $deleted_templates,
						'fstore'	=> $fstore,
						'svn'		=> $svn
					)
				);

			}
			if ($result === false) {
				echo "Update failed.\n";
				if (count($errs = $fstore->svnstack->getErrors())) {
					foreach ($errs as $err) {
						echo $err['message']."\n";
					}
				}
			}
		}
	}
?>