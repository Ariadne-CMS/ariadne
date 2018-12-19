<?php
	include_once($this->store->get_config("code")."modules/mod_yui.php");
?>
<script type="text/javascript">
	var callbacktarget = "extrapath";
	function callback(path) {
		document.getElementById(callbacktarget).value = path;
		document.getElementById("hidden_useradd").value = "useradd";
		document.getElementById("wgWizForm").submit();
	}
</script>
<?php
	$selectedpath = $this->getdata("selectedpath");
	if (!$selectedpath) {
		$selectedpath = $this->path;
	}

	$objects = array();
	// FIXME: Add grants check, make the objects array dynamic.
	$children = $this->find($this->path, '', 'system.get.phtml'); // FIXME: Default limit 100 isn't nice here.
	foreach ($children as $key => $child) {
		if ($child->path == $this->path || $child->data->config->grants) {
			$objects[$child->path] = array(
				"name" => $child->nlsdata->name,
				"type" => $child->type
			);
		}
	}

	$extrapaths = $this->getdata("extrapaths");
	if (is_array($extrapaths)) {
		foreach ($extrapaths as $key => $extrapath) {
			if ($objects[$extrapath] || !$this->exists($extrapath)) {
				unset($extrapaths[$key]);
				continue;
			} else {
				$extra_ob = current($this->get($extrapath, 'system.get.phtml'));
				$objects[$extra_ob->path] = array(
					"name" => $extra_ob->nlsdata->name,
					"type" => $extra_ob->type
				);
			}
		}
	}

	$pathadd = $this->getvar("pathadd");
	if ($pathadd) {
		if ($this->exists($extrapath)) {
			$selectedpath = $extrapath; // Select the new path.
		} else {
			$error = "Path $extrapath not found";
		}
	}

	$this->putvar('selectedpath', $selectedpath);
/*	$objects = array(
		"/sites/yvo/" => array(
			"name" => "My site",
			"type" => "psite"
		),
		"/sites/yvo/two/" => array(
			"name" => "Section two",
			"type" => "psection",
		),
		"/sites/yvo/two/contact/" => array(
			"name" => "Contact",
			"type" => "pdir",
		)
	);
*/
//	print_r($objects);
?>
<div class="items">
<h2><?php echo $ARnls['ariadne:grants:objects_with_grants']; ?></h2>
	<input type="hidden" name="selectedpath" value="<?php echo htmlspecialchars($selectedpath); ?>">
	<?php	if ($error) { ?>
		<div class="error"><?php echo $error; ?></div>
	<?php	} ?>
	<?php 	foreach ($objects as $path => $info) {
			$ob_id = str_replace("/", ":", $path);
	?>
			<label for="select_<?php echo $ob_id; ?>" class="block item <?php if($path == $selectedpath) { echo " selected";} ?>" title="<?php echo $info['name'];?>">
				<img src="<?php echo $this->call('system.get.icon.php', array('type' => $info['type'], 'size' => 'medium')); ?>" alt="<?php echo $info['type'];?>">
				<div class="object">
					<span class="name"><?php echo yui::labelspan($info['name'], 24); ?></span><br>
					<span class="path"><?php echo yui::labelspan($path, 24); ?></span>
				</div>
			</label>
			<input class="hidden" id="select_<?php echo $ob_id; ?>" type="submit" name="selectedpath" value="<?php echo $path; ?>">
	<?php	} ?>
</div>
<div class="browse">
	<?php
		if (is_array($extrapaths)) {
			foreach ($extrapaths as $extrapath) {
	?>
			<input type='hidden' name="extrapaths[]" value="<?php echo $extrapath; ?>">
	<?php
			}
		}
	?>
	<input type="text" id="extrapath" name="extrapaths[]" value="<?php echo $this->path; ?>">
	<input class="button" type="button" value="..." title="<?php echo $ARnls['browse']; ?>" onclick='callbacktarget="extrapath"; window.open("<?php echo $this->make_ariadne_url('/'); ?>" + document.getElementById("extrapath").value + "dialog.browse.php", "browse", "height=480,width=750"); return false;'>
	<input type="hidden" name="pathadd" value=''>
	<input type="submit" class="button" name="pathadd" value="<?php echo $ARnls['add']; ?>">
</div>
