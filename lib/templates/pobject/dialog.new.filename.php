<?php
	$ARCurrent->nolangcheck = true;
	if( $this->CheckLogin("read") && $this->CheckConfig() ) {
		$fname = $this->getdata("arNewFilename", "none");

		$autoNumberChecked = "";
		$inputDisabled = "";
		if ($fname == "{5:id}") {
			$autoNumberChecked = "checked";
			$inputDisabled = "disabled";
			$fname = "";
		}
?>
<script type="text/javascript">
	function toggleAutoNumber() {
		var checked = document.getElementById("autonumber").checked;
		if (checked) {
			document.getElementById("arNewFilename").disabled = true;
		} else {
			document.getElementById("arNewFilename").disabled = false;
		}
	}
</script>
<fieldset id="data">
	<legend><?php echo $ARnls["filename"]; ?></legend>
	<div class="right">
		<label for="autonumber">&nbsp;</label>
		<div class="field checkbox">
			<input onchange="toggleAutoNumber();" onclick="toggleAutoNumber();" <?php echo $autoNumberChecked; ?> type="checkbox" name="arNewFilename" value="{5:id}" id="autonumber" <?php if ($fname == "{5:id}") { echo "checked"; }?>>
			<label for="autonumber"><?php echo $ARnls["ariadne:new:autonumber"]; ?></label>
		</div>
	</div>
	<div class="left">
		<div class="field">
			<label for="arNewFilename" class="required"><?php echo $ARnls["filename"]; ?></label>
			<input id="arNewFilename" class="inputline wgWizAutoSelect wgWizAutoFocus" <?php echo $inputDisabled; ?> type="text" name="arNewFilename" value="<?php echo $fname ?>">
			<div class="help"><?php echo $ARnls['ariadne:new:lettersnumbers']; ?></div>
		</div>
	</div>
</fieldset>
<?php
	}
?>