<?php
	ldDisablePostProcessing();
	// FIXME: Make the non-javascript handling work as well.

	$ARCurrent->nolangcheck=true;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
	if ($this->CheckLogin("delete") && $this->CheckConfig()) {
		$target = $this->path;
		if ($this->getvar("childrenonly")) {
			$query = "object.parent ~= '$target%' order by path DESC";
		} else {
			$query = "object.path =~ '" . $target . "%' order by path DESC";
		}
		$total = $this->real_count_find($target, $query);
		$objects_left = $total;
		$offset = 0;

		// amount of objects to delete in one run. Smaller stepsize
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
<div id="deleting"><?php echo $ARnls["deleting"] . " " . $current_object_path; ?></div>
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
			$this->real_find($target, $query, "system.delete.phtml", array(), $stepsize, $offset); // Delete the $stepsize last items

			$new_objects_left = $this->real_count_find($target, $query);
			$next_object_path = current($this->real_find($target, $query, "system.get.path.phtml", array(), 1, $offset));
			if (strlen($next_object_path) > 25) {
				$next_object_path = substr($next_object_path, 0, 12) . "..." . substr($next_object_path, strlen($next_object_path) - 12, strlen($next_object_path));
			}

			$objects_deleted = $objects_left - $new_objects_left;
			$objects_skipped = $stepsize - $objects_deleted;
			$offset += $objects_skipped;
			$objects_left = $new_objects_left;
			$items_processed = $total-($objects_left-$offset);
			if ($items_processed > $total) {
				$items_processed = $total;
			}

			//echo "Deleted $stepsize items<br>";
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
			echo "document.getElementById('deleting').innerHTML = '" . $ARnls["deleting"] . " " . $next_object_path . "';\n";
			echo "</script>";
			flush();
		}?>
		<script type='text/javascript'>
			document.getElementById('progress_text').innerHTML = 'Done';
			document.getElementById('progress').style.width = '100%';
			document.getElementById('deleting').innerHTML = '<?php echo $ARnls["deleting"] . " " . $current_object_path; ?>';
			window.opener.muze.ariadne.explore.view('<?php echo ($this->getvar("childrenonly") ? $this->path : $this->parent );?>');
			window.close();
		</script>
<?php
	}
?>
