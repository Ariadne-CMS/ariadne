<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
?>
<fieldset id="data">
	<legend><?php echo $ARnls["data"]; ?></legend>
	<div class="field">
		<label for="name" class="required"><?php echo $ARnls["name"]; ?></label>
		<input id="name" type="text" name="name"
			value="<?php $this->showdata("name", "none"); ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="email"><?php echo $ARnls["email"]; ?></label>
		<input id="email" type="text" name="email" value="<?php $this->showdata("email", "none"); ?>" class="inputline">
	</div>
</fieldset>

<?php } ?>
