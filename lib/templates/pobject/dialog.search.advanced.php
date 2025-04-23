<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
		$arPath = $this->getdata("arPath", "none");
		if( !$arPath ) {
			$arPath = $this->path;
		}
		$query = $this->call("dialog.search.results.query.php", array("advanced" => 1, "context" => 0 ));
?>
<fieldset id="context">
	<legend><?php echo $ARnls["advancedsearch"]; ?></legend>
	<div class="field">
		<label for="arPath" class="required"><?php echo $ARnls["path"]; ?></label>
		<input id="arPath" type="text" name="arPath" value="<?php echo $arPath; ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="query"><?php echo $ARnls["ariadne:query"]; ?></label>
		<textarea id="query" name="query" class="inputbox" rows="5" cols="42"><?php
			echo htmlentities($query??'', ENT_QUOTES, 'UTF-8');
		?></textarea>
	</div>
</fieldset>
<?php
		$this->call("dialog.search.results.php", array("advanced" => 1) );
	}
?>
