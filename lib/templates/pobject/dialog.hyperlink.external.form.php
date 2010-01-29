<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$arEditorSettings = $this->call("editor.ini");
?>
<fieldset id="data" class="browse">
		<legend><?php echo $ARnls["path"]; ?></legend>
		<div class="field">
			<input type="hidden" name="type" value="external">
			<label for="url" class="required"><?php echo $ARnls["path"]; ?></label>
			<input type="text" id="url" name="url" value="<?php echo htmlspecialchars($this->getvar('url')); ?>" class="inputline wgWizAutoFocus">
		</div>
		<div class="field">
			<label for="anchor"><?php echo $ARnls["ariadne:editor:anchor"]; ?></label>
			<input type="text" id="anchor" name="anchor" value="<?php echo htmlspecialchars($this->getvar("anchor")); ?>" class="inputline">
		</div>
		<div class="field">
			<label for="behaviour"><?php echo $ARnls["ariadne:editor:behaviour"]; ?></label>
			<select name="behaviour" id="behaviour">
				<?php foreach ($arEditorSettings['link']['types']['internal']['options']['behaviour'] as $behaviour) { ?>
					<option value="<?php echo $behaviour; ?>"><?php echo $arEditorSettings['link']['behaviours'][$behaviour]['name']; ?></option>
				<?php	} ?>
			</select>
		</div>
</fieldset>
<?php
	}
?>