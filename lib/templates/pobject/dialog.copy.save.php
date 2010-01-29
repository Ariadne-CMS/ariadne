<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin('config') && $this->CheckConfig()) {
		$copytarget = $this->getdata("target");
		if (substr($copytarget, -1) != "/") {
			$copytarget .= "/";
		}
		if ($this->exists($copytarget)) {
			$copytarget = $copytarget . basename($this->path) . "/";
		}
		// FIXME: add more checks to make sure the target to copy to will work as expected.

		$copytarget_ob = current($this->get($this->make_path($copytarget . '../'), "system.get.phtml"));
		$target_typetree = $copytarget_ob->call("typetree.ini");
		if (
			$target_typetree[$copytarget_ob->type][$this->type] || // The object is allowed under the target by the typetree.
			($this->CheckSilent('layout') && $this->getvar('override_typetree')) // Layout grant allows typetree changes, so allow typetree overrides as well.
		) { 
			// This type is allowed.
			$query = "object.path =~ '" . $this->path . "%' order by path ASC";

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
	<div class="buttons">
	<div class="left">
	<input unselectable="on" type="submit" name="wgWizControl" class="wgWizControl" onClick="document.wgWizForm.wgWizAction.value='cancel';" value="<?php echo $ARnls['cancel']; ?>">
	</div>
	</div>
	<?php 
			while ($offset < $objects_left) {
				flush();
				set_time_limit(30);
				$objects = $this->find($this->path, $query, "system.get.phtml", array(), $stepsize, $offset); // copyto the $stepsize last items

				foreach ($objects as $object) {
					$sourcepath = $object->path;
					$targetpath = $copytarget . substr($sourcepath, strlen($this->path), strlen($sourcepath));
					$this->get($sourcepath, "system.copyto.phtml", array("target" => $targetpath));
				}

				$new_objects_left = $this->count_find($target, $query);
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
			}?>
			<script type='text/javascript'>
				document.getElementById('progress_text').innerHTML = 'Done';
				document.getElementById('progress').style.width = '100%';
				document.getElementById('copy').innerHTML = '<?php echo $ARnls["copying"] . " " . $current_object_path; ?>';
				// FIXME: Add copied path to the tree?
				currentpath = window.opener.muze.ariadne.registry.get('path');
				if (currentpath == '<?php echo $this->make_path($copytarget . "../"); ?>') {
					window.opener.muze.ariadne.explore.objectadded();
				} else {
					window.opener.muze.ariadne.explore.tree.view('<?php echo $this->make_path($copytarget . "../"); ?>');
				}
				window.close();
			</script>
<?php
		} else {
			echo $ARnls['err:typetree_does_not_allow'];
		}
	}
?>