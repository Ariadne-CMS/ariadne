<?php
	ldDisablePostProcessing();
	$ARCurrent->nolangcheck=true;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
	if ($this->CheckLogin('edit') && $this->CheckConfig()) {
		// FIXME: Check vars first;

		$oldurl = $this->getvar('oldurl');
		$newurl = $this->getvar('newurl');

		$oldreference = $this->getvar('oldreference');
		$newreference = $this->getvar('newreference');

		if ($oldurl && $newurl) {
			$rewrite_urls = array(
				$oldurl => $newurl
			);
		};

		if ($oldreference && $newreference) {
			$rewrite_references = array(
				$oldreference => $newreference
			);
		};

		if ($rewrite_urls || $rewrite_references) {
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
			<div id="rewrite"><?php echo $ARnls["ariadne:rewriting"] . " " . $current_object_path; ?></div>
			<div id="progressbar">
				<div id="progress"></div>
				<div id="progress_text">0/<?php echo $total; ?></div>
			</div>
			<?php

			do {
				flush();
				set_time_limit(30);
				$objects = $this->find($this->path, $query, "system.get.phtml", array(), $stepsize, $offset);

				foreach ($objects as $object) {
					$error = false;
					$error = current($this->get($object->path, "system.rewrite.urls.php", array("rewrite_urls" => $rewrite_urls)));
					if (!$error) {
						$error = current($this->get($object->path, "system.rewrite.htmlblocks.php", array("rewrite_urls" => $rewrite_urls)));
						if (!$error) {
							$error = current($this->get($object->path, "system.rewrite.references.php", array("rewrite_references" => $rewrite_references)));
						}
					}

					if ($error) {
						?>
						<script type="text/javascript">
							alert("<?php echo addslashes($error); ?>");
						</script>
						<?php
						return false;
					}

				}

				$new_objects_left = $objects_left - sizeof($objects);
				$next_object_path = current($this->find($this->path, $query, "system.get.path.phtml", array(), 1, $offset));
				if (strlen($next_object_path) > 25) {
					$next_object_path = substr($next_object_path, 0, 12) . "..." . substr($next_object_path, strlen($next_object_path) - 12, strlen($next_object_path));
				}

				$objects_handled = $objects_left - $new_objects_left;
				$objects_skipped = $stepsize - $objects_handled;
				$offset += count( $objects );
				$objects_left = $new_objects_left;
				$items_processed = $total-($objects_left-$offset);
				if ($items_processed > $total) {
					$items_processed = $total;
				}

				//echo "Changed owner for $stepsize items<br>";
				$progress = (int)(100*($offset)/$total);

				if ($progress < 0) {
					$progress = 0;
				}
				if ($progress > 100) {
					$progress = 100;
				}
				echo "<script type='text/javascript'>\n";
				echo "document.getElementById('progress').style.width = '" . $progress . "%';\n";
				echo "document.getElementById('progress_text').innerHTML = '" . $offset . "/" . $total . "';\n";
				echo "document.getElementById('rewrite').innerHTML = '" . $ARnls["ariadne:rewriting"] . " " . $next_object_path . "';\n";
				echo "</script>";
				flush();
			} while (count($objects) == $stepsize);

?>
			<script type='text/javascript'>
				document.getElementById('progress_text').innerHTML = 'Done';
				document.getElementById('progress').style.width = '100%';
				document.getElementById('rewrite').innerHTML = '<?php echo $ARnls["ariadne:rewriting"] . " " . $current_object_path; ?>';

				if ( window.opener && window.opener.muze && window.opener.muze.ariadne && window.opener.muze.ariadne.registry ) {
					window.opener.muze.ariadne.explore.objectadded();
				}
				window.close();
			</script>
<?php
		} else {
			echo $ARnls['ariadne:err:rewrite.no_input'];
		}
	}
?>
