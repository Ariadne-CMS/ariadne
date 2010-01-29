<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$arEditorSettings = $this->call("editor.ini");
		$arpath = $this->getdata('arpath');
		if (!$arpath) {
			$arpath = $this->path;
		}
?>
<script type="text/javascript">
	function callback(path) {
		document.getElementById("target").value = path;
	}
</script>
<fieldset id="data" class="browse">
		<legend><?php echo $ARnls["path"]; ?></legend>
		<div class="field">
			<input type="hidden" name="type" value="internal">
			<label for="target" class="required"><?php echo $ARnls["path"]; ?></label>
			<input type="text" id="target" name="target" value="<?php echo htmlspecialchars($arpath); ?>" class="inputline wgWizAutoFocus">
			<input class="button" type="button" value="<?php echo $ARnls['browse']; ?>" title="<?php echo $ARnls['browse']; ?>" onclick='callbacktarget="extrauser"; window.open("<?php echo $this->make_local_url($wgBrowsePath); ?>" + "dialog.browse.php", "browse", "height=480,width=750"); return false;'>
		</div>
		<div class="field">
			<label for="anchor"><?php echo $ARnls["ariadne:editor:anchor"]; ?></label>
			<input type="text" id="anchor" name="anchor" value="<?php echo htmlspecialchars($this->getvar("anchor")); ?>" class="inputline">
		</div>
		<div class="field">
			<label for="language"><?php echo $ARnls["ariadne:editor:language"]; ?></label>
			<select name="language" id="language">
				<?php foreach ($arEditorSettings['link']['types']['internal']['options']['language'] as $key => $language) { ?>
					<option value="<?php echo $key; ?>"><?php echo $language; ?></option>
				<?php	} ?>
			</select>
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