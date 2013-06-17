<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$arEditorSettings = $this->call("editor.ini");

		$url = $this->getvar('url');
		$arbehaviour = $this->getvar('arbehaviour');
		$aranchor = $this->getvar('aranchor');

		$matches = array();
		if (preg_match('|(.*)#([^#]*)$|', $url, $matches)) {
			if (!$aranchor) {
				$aranchor = $matches[2];
			}
			$url = $matches[1];
		}
?>
<fieldset id="data" class="browse">
		<legend><?php echo $ARnls["path"]; ?></legend>
		<div class="field">
			<input type="hidden" name="artype" value="external">
			<label for="url" class="required"><?php echo $ARnls["url"]; ?></label>
			<input type="text" id="url" name="url" value="<?php echo htmlspecialchars($url); ?>" class="inputline wgWizAutoFocus">
		</div>
		<div class="field">
			<label for="aranchor"><?php echo $ARnls["ariadne:editor:anchor"]; ?></label>
			<input type="text" id="aranchor" name="aranchor" value="<?php echo htmlspecialchars($aranchor); ?>" class="inputline">
		</div>
		<div class="field">
			<label for="arbehaviour"><?php echo $ARnls["ariadne:editor:behaviour"]; ?></label>
			<select name="arbehaviour" id="arbehaviour">
				<?php
					foreach ($arEditorSettings['link']['types']['external']['options']['behaviour'] as $behaviour) {
						$selected = "";
						if ($behaviour == $arbehaviour) {
							$selected = " selected";
						}
						echo "<option value=\"$behaviour\"$selected>" . $arEditorSettings['link']['behaviours'][$behaviour]['name'] . "</option>\n";
					}
				?>
			</select>
		</div>
</fieldset>
<?php
	}
?>