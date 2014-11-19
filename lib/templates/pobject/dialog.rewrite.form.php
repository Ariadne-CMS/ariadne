<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("edit") && $this->CheckConfig()) {
?>
<fieldset id="url">
	<legend><?php echo $ARnls["ariadne:rewrite.urls"]; ?></legend>
	<div class="field">
		<label for="oldurl" class="required"><?php echo $ARnls["ariadne:rewrite.oldurl"]; ?></label>
		<input id="oldurl" type="text" name="oldurl"
			value="<?php $this->getvar("oldurl"); ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="newurl" class="required"><?php echo $ARnls["ariadne:rewrite.newurl"]; ?></label>
		<input id="newurl" type="text" name="newurl"
			value="<?php $this->getvar("newurl"); ?>" class="inputline">
	</div>
</fieldset>

<fieldset id="reference">
	<legend><?php echo $ARnls["ariadne:rewrite.references"]; ?></legend>
	<div class="field">
		<label for="oldreference" class="required"><?php echo $ARnls["ariadne:rewrite.oldreference"]; ?></label>
		<input id="oldreference" type="text" name="oldreference"
			value="<?php $this->getvar("oldreference"); ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="newreference" class="required"><?php echo $ARnls["ariadne:rewrite.newreference"]; ?></label>
		<input id="newreference" type="text" name="newreference"
			value="<?php $this->getvar("newreference"); ?>" class="inputline">
	</div>
</fieldset>

<?php } ?>
