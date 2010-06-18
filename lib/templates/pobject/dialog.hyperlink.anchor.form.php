<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$arEditorSettings = $this->call("editor.ini");
?>
<fieldset id="data" class="browse">
		<legend><?php echo $ARnls["path"]; ?></legend>
		<div class="field">
			<input type="hidden" name="type" value="anchor">
			<label for="name"><?php echo $ARnls["ariadne:editor:anchor"]; ?></label>
			<input type="text" id="name" name="name" value="<?php echo htmlspecialchars($this->getvar("name")); ?>" class="inputline">
		</div>
</fieldset>
<?php
	}
?>