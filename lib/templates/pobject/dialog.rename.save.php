<?php
	$ARCurrent->nolangcheck=true;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
//	if ($this->CheckLogin("delete") && $this->CheckConfig()) {

		if ($this->getvar("sources")) {
			$sources = $this->getvar('sources');
		} else {
			$sources = array($this->path);
		}

                //hier check of add-op-target toegestaan is?

		foreach($sources as $source) {
			$source_ob = current($this->get($source, "system.get.phtml"));

			$target = $this->getvar("target");
			if (substr($target, -1) != "/") {
				$target .= "/";
			}

			if (
				strtolower($target) != strtolower($source_ob->path) &&
				$this->exists($target)
			) {
				$target = $target . basename($source_ob->path) . "/";
			}

			$target_parent_path = $this->make_path($target . "../");

			if (!$this->exists($target_parent_path)) {
				$this->error=sprintf($ARnls["err:noparentcreatefirst"],$target_parent_path);
			} else {
				$target_ob = current($this->get($target_parent_path, "system.get.phtml"));
				$target_typetree = $target_ob->call("typetree.ini");

				if (
					$target_typetree[$target_ob->type][$source_ob->type] || 	// The object is allowed under the target by the typetree.
					($target_parent_path == $source_ob->parent) || 				// The object is not moved, just gets a new filename
					($source_ob->CheckSilent('layout') && $this->getvar('override_typetree')) // Layout grant allows typetree changes, so allow typetree overrides as well.
				) {
					// This type is allowed.

					$source_ob->call("system.rename.phtml", array("target" => $target)); // $arCallArgs); // system.rename will fix the path by itself and do more checks, so do not pass $target to it from here.
					if ($source_ob->error) {
						$this->error .= $source_ob->nlsdata->name . ": ". $source_ob->error . "<br>";
					}
				} else {
					$this->error = $ARnls['err:typetree_does_not_allow'];
				}
			}
		}

		$returnpath = $this->path;
		while (($returnpath != '/') && (!$this->exists($returnpath))) {
			$returnpath = $this->make_path($returnpath . "../");
		}

		if ($this->error) {
			echo "<font color='red'>$this->error</font><br>";
		} else {
			// FIXME: update the tree and explore window.
			?>
			<script type="text/javascript">
				if ( window.opener && window.opener.muze && window.opener.muze.dialog ) {
					window.opener.muze.dialog.callback( window.name, 'renamed', {
						'path': '<?php echo $target; ?>',
						'url' : '<?php echo $this->make_url($target); ?>'
					});
				} else {
					// FIXME: Add copied path to the tree?
					if ( window.opener && window.opener.muze && window.opener.muze.ariadne  ) {
							window.opener.muze.ariadne.explore.tree.refresh('<?php echo $returnpath; ?>');
						window.opener.muze.ariadne.explore.view('<?php echo $returnpath; ?>');
					}
					window.close();
				}
			</script>
			<?php
		}
//	}
?>
