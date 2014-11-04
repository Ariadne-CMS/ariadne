<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
?>
<fieldset id="contact">
	<legend><?php echo $ARnls["contactinformation"]; ?></legend>
	<div class="field">
		<label for="telephone" class="required"><?php echo $ARnls["telephone"]; ?></label>
		<input id="telephone" type="text" name="telephone" value="<?php $this->showdata("telephone", "none"); ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="mobile" class="required"><?php echo $ARnls["mobile"]; ?></label>
		<input id="mobile" type="text" name="mobile" value="<?php $this->showdata("mobile", "none"); ?>" class="inputline">
	</div>
	<div class="field">
		<label for="emails" class="required"><?php echo $ARnls["email"]; ?></label>
		<?php
			$wgMultipleArray=$this->getdata("emails","none");
			if (!is_array($wgMultipleArray) && ($wgMultipleArray)) {
					$wgMultipleArray=explode(",",$wgMultipleArray);
			}
			$wgMultipleName="emails";
			include($this->store->get_config("code")."widgets/multiple/js.html");
			include($this->store->get_config("code")."widgets/multiple/form.html");
		?>
	</div>
</fieldset>
<?php } ?>
