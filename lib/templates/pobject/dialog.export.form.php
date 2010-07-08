<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
?>
	<!--fieldset>
		<legend><?php echo $ARnls["selectexporttype"]; ?></legend>
		<div class="field radio">
			<input id="ax" type="radio" name="exporttype" value="ax" checked>
			<label for="ax">AX</label>
		</div>
		<div class="field radio">
			<input id="wddx" type="radio" name="exporttype" value="wddx">
			<label for="wddx">WDDX</label>
		</div>
	</fieldset-->
	<fieldset>
		<legend><?php echo $ARnls["options"]; ?></legend>
		<div class="field radio">
			<input id="without_grants" type="checkbox" name="without_grants" value="1" checked>
			<label for="without_grants"><?php echo $ARnls["withoutgrants"]; ?></label>
		</div>
		<div class="field radio">
			<input id="full_path" type="checkbox" name="full_path" value="1">
			<label for="full_path"><?php echo $ARnls["fullpath"]; ?></label>
		</div>
	</fieldset>
	<fieldset>
		<legend><?php echo $ARnls["advanced"]; ?></legend>
		<div class="field radio">
			<input id="without_data" type="checkbox" name="without_data" value="1">
			<label for="without_data"><?php echo $ARnls["withoutdata"]; ?></label>
		</div>
		<div class="field radio">
			<input id="without_files"  type="checkbox" name="without_files" value="1">
			<label for="without_files"><?php echo $ARnls["withoutfiles"]; ?></label>
		</div>
		<div class="field radio">
			<input id="without_templates" type="checkbox" name="without_templates" value="1">
			<label for="without_templates"><?php echo $ARnls["withouttemplates"]; ?></label>
		</div>
	</fieldset>
<?php
	}
?>