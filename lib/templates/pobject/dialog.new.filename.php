<?php
	$ARCurrent->nolangcheck = true;
	if( $this->CheckLogin("read") && $this->CheckConfig() ) {
		$fname = $this->getdata("arNewFilename", "none");
		if( !$fname ) {
			$fname = "{5:id}";
		}
?>
<fieldset id="data">
	<legend><?php echo $ARnls["filename"]; ?></legend>
	<div class="field">
		<label for="arNewFilename" class="required"><?php echo $ARnls["filename"]; ?></label>
		<input id="arNewFilename" class="inputline wgWizAutoSelect wgWizAutoFocus" type="text" name="arNewFilename" value="<?php echo $fname ?>">
	</div>
</fieldset>
<?php
	}
?>