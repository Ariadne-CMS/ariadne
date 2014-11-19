<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$arEditorSettings = $this->call("editor.ini");
		$name = $this->getvar('name');
?>
<fieldset id="data" class="browse">
		<legend><?php echo $ARnls["ariadne:editor:anchor"]; ?></legend>
		<div class="field">
			<input type="hidden" name="artype" value="anchor">
			<label for="name"><?php echo $ARnls["name"]; ?></label>
			<input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" class="inputline">
		</div>
</fieldset>
<?php
	}
?>
