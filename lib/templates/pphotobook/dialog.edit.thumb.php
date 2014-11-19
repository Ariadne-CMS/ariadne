<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
?>
<fieldset id="thumbnail">
	<legend><?php echo $ARnls["thumbnail"]; ?></legend>
	<div class="field">
		<label for="thumbwidth"><?php echo $ARnls["thumbwidth"]; ?></label>
		<input id="thumbwidth" type="text" name="thumbwidth" value="<?php $this->showdata("thumbwidth", "none"); ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="thumbheight"><?php echo $ARnls["thumbheight"]; ?></label>
		<input id="thumbheight" type="text" name="thumbheight" value="<?php $this->showdata("thumbheight", "none"); ?>" class="inputline">
	</div>
	<div class="field">
		<label for="thumbcolor"><?php echo $ARnls["thumbcolor"]; ?></label>
		<input id="thumbcolor" type="text" name="thumbcolor" value="<?php $this->showdata("thumbcolor", "none"); ?>" class="inputline">
	</div>
</fieldset>
<?php
	}
?>
