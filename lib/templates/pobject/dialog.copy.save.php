<?php
	ldDisablePostProcessing();
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin('read') && $this->CheckConfig()) {

		$copytarget = $this->make_path($this->getvar('target'));
		$copytargetparent = $this->make_path($copytarget.'..');
		if ($this->exists($copytarget)) {
			$copytarget = $copytarget;
		} else if (!$this->exists($copytargetparent)) {
			$this->error = sprintf($ARnls["err:filenameinvalidnoparent"], $copytarget, $copytargetparent);
			return error($this->error);
		}
		// FIXME: add more checks to make sure the target to copy to will work as expected.

                if ($this->getvar("sources")) {
			$sources = $this->getvar('sources');
		} else {
			$sources = array($this->path);
		}

		$copytarget_ob = current($this->get($this->make_path($copytarget . '../'), "system.get.phtml"));
		if ($copytarget_ob->CheckLogin('add')) {
			$target_typetree = $copytarget_ob->call("typetree.ini");
			
                        $failedchecks = false;
                        
                        $query = "(";
                        foreach ($sources as $source) {
                            if (
                                    $target_typetree[$copytarget_ob->type][$this->type] || // The object is allowed under the target by the typetree.
                                    ($this->CheckSilent('layout') && $this->getvar('override_typetree')) // Layout grant allows typetree changes, so allow typetree overrides as well.
                            ) { 
                                    // This type is allowed.

                                //dit is de nieuwe query
                                $query .= "object.path =~ '" . $source . "%' OR ";
                            } else {
                                $failedchecks = true;
                            }
                        }
                        $query = substr($query, 0, -3);
                        $query .= ") order by path ASC";

                        //echo $query;
                        //exit;
                        
                        if (!$failedchecks) {
				$total = $this->count_find($this->path, $query);
				$objects_left = $total;
				$offset = 0;

				// amount of objects to process in one run. Smaller stepsize
				// gives more updates but takes a bit longer. This way there
				// are usually 20 steps to give a nice resolution on the
				// updates.

				$stepsize = (int)($total/20);
				if ($stepsize < 5) {
					$stepsize = 5;
				}
				if ($stepsize > 100) {
					$stepsize = 100;
				}

				$current_object_path = $this->path;
				if (strlen($current_object_path) > 25) {
					$current_object_path = substr($this->path, 0, 12) . "..." . substr($this->path, strlen($this->path) - 12, strlen($this->path));
				}
		?>
		<div id="copy"><?php echo $ARnls["copying"] . " " . $current_object_path; ?></div>
		<div id="progressbar">
			<div id="progress"></div>
			<div id="progress_text">0/<?php echo $total; ?></div>
		</div>
		<?php 
				while ($offset < $objects_left) {
					flush();
					set_time_limit(30);
					$objects = $this->find($this->path, $query, "system.get.phtml", array(), $stepsize, $offset); // copyto the $stepsize last items

					foreach ($objects as $object) {
						$sourcepath = $object->path;
                                                
		                                $targetpath = $copytarget;
                                                foreach ($sources as $sourceparent) {
                                                	if (strstr($sourcepath, $sourceparent)) {
                                                        	$targetpath .= basename($sourceparent) . "/";
								$targetpath .= substr($sourcepath, strlen($sourceparent), strlen($sourcepath));
								break;
							}
						}

                                                $targetpath = preg_replace("|//|", "/", $targetpath);
                                                
                                                // exit;
						$error = current($this->get($sourcepath, "system.copyto.phtml", array("target" => $targetpath)));
						if ($error) {
							?>
							<script type="text/javascript">
								alert("<?php echo addslashes($error); ?>");
							</script>
							<?php
							return false;
						}
					}

					$new_objects_left = $objects_left; //$this->count_find($copytarget, $query); // FIXME: Deze is hier onzinnig.

					$next_object_path = current($this->find($this->path, $query, "system.get.path.phtml", array(), 1, $offset));
					if (strlen($next_object_path) > 25) {
						$next_object_path = substr($next_object_path, 0, 12) . "..." . substr($next_object_path, strlen($next_object_path) - 12, strlen($next_object_path));
					}

					$objects_handled = $objects_left - $new_objects_left;
					$objects_skipped = $stepsize - $objects_handled;
					$offset += $objects_skipped;
					$objects_left = $new_objects_left;
					$items_processed = $total-($objects_left-$offset);
					if ($items_processed > $total) {
						$items_processed = $total;
					}

					//echo "Changed owner for $stepsize items<br>";
					$progress = (int)(100*($items_processed)/$total);

					if ($progress < 0) {
						$progress = 0;
					}
					if ($progress > 100) {
						$progress = 100;
					}
					echo "<script type='text/javascript'>\n";
					echo "document.getElementById('progress').style.width = '" . $progress . "%';\n";
					echo "document.getElementById('progress_text').innerHTML = '" . $items_processed . "/" . $total . "';\n";
					echo "document.getElementById('copy').innerHTML = '" . $ARnls["copying"] . " " . $next_object_path . "';\n";
					echo "</script>";
					flush();
				}

				$ARCurrent->newpath = $copiedTo;

				?>
				<script type='text/javascript'>
					document.getElementById('progress_text').innerHTML = 'Done';
					document.getElementById('progress').style.width = '100%';
					document.getElementById('copy').innerHTML = '<?php echo $ARnls["copying"] . " " . $current_object_path; ?>';
					if ( window.opener && window.opener.muze && window.opener.muze.dialog && window.opener.muze.ariadne.registry ) {
						window.opener.muze.dialog.callback( window.name, 'copied', { 
							'copyTarget': '<?php echo $this->make_path($copytarget . "../"); ?>',
							'path': window.opener.muze.ariadne.registry.get('path')
						});
					} else {
						// FIXME: Add copied path to the tree?
						if ( window.opener && window.opener.muze && window.opener.muze.ariadne && window.opener.muze.ariadne.registry ) {
							currentpath = window.opener.muze.ariadne.registry.get('path');
							if (currentpath == '<?php echo $this->make_path($copytarget . "../"); ?>') {
								window.opener.muze.ariadne.explore.objectadded();
							} else {
								window.opener.muze.ariadne.explore.tree.view('<?php echo $this->make_path($copytarget . "../"); ?>');
							}
						}
						window.close();
					}
				</script>
<?php
                    } else {
                        echo $ARnls['err:typetree_does_not_allow'];
                    }                
		} else {
			echo $ARnls['err:no_add_on_target'];
		}
	}
?>