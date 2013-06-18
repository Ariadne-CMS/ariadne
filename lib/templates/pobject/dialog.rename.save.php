<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("delete") && $this->CheckConfig()) {
		$target = $this->getvar("target");
		if (substr($target, -1) != "/") {
			$target .= "/";
		}
		if ($this->exists($target)) {
			$target = $target . basename($this->path) . "/";
		}

		$target_parent_path = $this->make_path($target . "../");

		if (!$this->exists($target_parent_path)) {
			$this->error=sprintf($ARnls["err:noparentcreatefirst"],$target_parent_path);			
		} else {
			$target_ob = current($this->get($target_parent_path, "system.get.phtml"));
			$target_typetree = $target_ob->call("typetree.ini");
		
			if (
				$target_typetree[$target_ob->type][$this->type] || 	// The object is allowed under the target by the typetree.
				($target_parent_path == $this->parent) || 				// The object is not moved, just gets a new filename
				($this->CheckSilent('layout') && $this->getvar('override_typetree')) // Layout grant allows typetree changes, so allow typetree overrides as well.
			) {
				// This type is allowed.
			
				$folder=$this->call("system.get.folder.phtml");
				$oldlocation=$this->path;
				$this->call("system.rename.phtml", $arCallArgs); // system.rename will fix the path by itself and do more checks, so do not pass $target to it from here.
				if (!$this->error) {
					// FIXME: update the tree and explore window.
					?>
						<script type="text/javascript">
							if ( window.opener && window.opener.muze && window.opener.muze.dialog ) {
								window.opener.muze.dialog.callback( window.name, 'renamed', { 
									'path': '<?php echo $this->path; ?>'
								});
							} else {
								// FIXME: Add copied path to the tree?
								if ( window.opener && window.opener.muze && window.opener.muze.ariadne  ) {
									window.opener.muze.ariadne.explore.tree.refresh('<?php echo $this->path; ?>');
									window.opener.muze.ariadne.explore.view('<?php echo $this->path; ?>');
								}
								window.close();
							}
						</script>
					<?php
				}
			} else {
				echo $ARnls['err:typetree_does_not_allow'];
			}
		}
		if ($this->error) {
			echo "<font color='red'>$this->error</font>";
		}
	}
?>