<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$target = $this->getvar("target");
		if (!$target) {
			$target = $this->path;
		}
?>
<script type="text/javascript">
	function callback(path) {
		document.getElementById("target").value = path;
	}
</script>
<fieldset id="data" class="browse">
		<legend><?php echo $ARnls["path"]; ?></legend>
		<div class="field">
			<label for="target" class="required"><?php echo $ARnls["target"]; ?></label>
			<input id="target" type="text" name="target" value="<?php echo $target; ?>" class="inputline wgWizAutoFocus">
			<input class="button" type="button" value="<?php echo $ARnls['browse']; ?>" title="<?php echo $ARnls['browse']; ?>" onclick='window.open("<?php echo $this->make_ariadne_url('/'); ?>" + document.getElementById("target").value + "dialog.browse.php", "browse", "height=480,width=750"); return false;'>
			<div class="clear"></div>
		</div>
<?php 		if ($this->CheckSilent("layout")) { ?>
		<div class="field checkbox">
			<input id="override_typetree" type="checkbox" name="override_typetree" value="1">
			<label for="override_typetree"><?php echo $ARnls['ariadne:override_typetree']; ?></label>
		</div>
<?php		} ?>
</fieldset>
<?php	} 
?>