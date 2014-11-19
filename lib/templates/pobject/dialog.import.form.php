<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("config") && $this->CheckConfig()) {

		if ($this->error) {
			echo "<div class=\"field\">\n";
				error($this->error);
			echo "</div>\n";
		}
?>
		<div class="field file">
			<label for="source"><?php echo $ARnls["file"].":&nbsp;"; ?></label>
			<input id="source" type="file" name="source" class="autoSized wgWizAutoFocus">
		</div>
		<fieldset>
			<legend><?php echo $ARnls["options"]; ?></legend>
			<div class="field checkbox">
				<input id="without_grants" type="checkbox" class="autoSized" name="without_grants" value="1" checked>
				<label for="without_grants"><?php echo $ARnls["withoutgrants"]; ?></label>
			</div>
			<div class="field checkbox">
				<input id="force" type="checkbox" class="autoSized" name="force" value="1">
				<label for="force"><?php echo $ARnls["force"]; ?></label>
			</div>
		</fieldset>
		<fieldset>
			<legend><?php echo $ARnls["advanced"]; ?></legend>
			<div class="field checkbox">
				<input id="without_data" type="checkbox" class="autoSized" name="without_data" value="1">
				<label for="without_data"><?php echo $ARnls["withoutdata"]; ?></label>
			</div>
			<div class="field checkbox">
				<input id="without_files" type="checkbox" class="autoSized" name="without_files" value="1">
				<label for="without_files"><?php echo $ARnls["withoutfiles"]; ?></label>
			</div>
			<div class="field checkbox">
				<input id="without_templates" type="checkbox" class="autoSized" name="without_templates" value="1">
				<label for="without_templates"><?php echo $ARnls["withouttemplates"]; ?></label>
			</div>
		</fieldset>
<?php
	}
?>
