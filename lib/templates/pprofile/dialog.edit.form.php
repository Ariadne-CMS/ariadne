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
	<?php
		if ( $arNewType ) {
			$this->call('dialog.edit.form.scaffolds.php', $this->getvar('arCallArgs'));
		}
	?>
</fieldset>
<?php
	}
?>
