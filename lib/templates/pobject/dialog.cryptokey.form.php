<?php
	$ARCurrent->nolangcheck=true;
	$key = "";
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$key = ar('security/crypt')->generateKey();
	}

?>
	<fieldset id="data">
			<legend><?php echo $ARnls["ariadne:grantkey"]; ?></legend>
			<div class="field">
				<label for="grantkey"><?php echo $ARnls["ariadne:cryptokey"]; ?></label>
				<input type="text" value="<?php echo $key; ?>" id="key" name="key" class="inputline  wgWizAutoFocus wgWizAutoSelect">
			</div>
	</fieldset>
