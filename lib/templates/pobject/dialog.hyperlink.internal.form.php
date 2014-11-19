<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$arEditorSettings = $this->call("editor.ini");
		$arpath = $this->getdata('arpath');
		if (!$arpath) {
			$arpath = $this->path;
		}
		$arbehaviour = $this->getvar('arbehaviour');
		$arlanguage  = $this->getvar('arlanguage');
		$aranchor    = $this->getvar('aranchor');
		$url         = $this->getvar('url');
		$root        = $this->getvar('root');
		$arnofollow  = ($this->getvar('rel') == "nofollow");

		$matches = array();
		if (preg_match('|(.*)#([^#]*)$|', $url, $matches)) {
			if (!$aranchor) {
				$aranchor = $matches[2];
			}
			$url = $matches[1];
		}
?>
<script type="text/javascript">
	function callback(path) {
		document.getElementById("arpath").value = path;
	}

	function hyperlinkBrowse( path ) {
		var objectURL = "<?php echo $this->make_ariadne_url($wgBrowsePath); ?>";
		var linkURL = muze.load( objectURL + 'dialog.makeLocalURL.ajax?path=' + escape(path), true, false );
		if (!linkURL) {
			linkURL = objectURL;
		}
		window.open( linkURL + "dialog.browse.php?root=<?php echo urlencode($root); ?>", "browse", "height=480,width=750");
		return false;
	}

</script>
<fieldset id="data" class="browse">
		<legend><?php echo $ARnls["path"]; ?></legend>
		<div class="field">
			<input type="hidden" name="artype" value="internal">
			<label for="arpath" class="required"><?php echo $ARnls["path"]; ?></label>
			<input type="text" id="arpath" name="arpath" value="<?php echo htmlspecialchars($arpath); ?>" class="inputline wgWizAutoFocus">
			<input class="button" type="button" value="<?php echo $ARnls['browse']; ?>" title="<?php echo $ARnls['browse']; ?>" onclick="return hyperlinkBrowse(document.getElementById('arpath').value);">
		</div>
		<div class="field">
			<label for="aranchor"><?php echo $ARnls["ariadne:editor:anchor"]; ?></label>
			<input type="text" id="aranchor" name="aranchor" value="<?php echo htmlspecialchars($aranchor); ?>" class="inputline">
		</div>
		<div class="field">
			<label for="arlanguage"><?php echo $ARnls["ariadne:editor:language"]; ?></label>
			<select name="arlanguage" id="arlanguage">
				<option value=""><?php echo $ARnls['none']; ?></option>
				<?php
					foreach ($arEditorSettings['link']['types']['internal']['options']['language'] as $key => $language) {
						$selected = "";
						if ($key == $arlanguage) {
							$selected = " selected";
						}
						echo "<option value=\"$key\"$selected>$language</option>\n";
					}
				?>
			</select>
		</div>
		<div class="field">
			<label for="arbehaviour"><?php echo $ARnls["ariadne:editor:behaviour"]; ?></label>
			<select name="arbehaviour" id="arbehaviour">
				<?php
					foreach ($arEditorSettings['link']['types']['internal']['options']['behaviour'] as $behaviour) {
						$selected = "";
						if ($behaviour == $arbehaviour) {
							$selected = " selected";
						}
						echo "<option value=\"$behaviour\"$selected>" . $arEditorSettings['link']['behaviours'][$behaviour]['name'] . "</option>\n";
					}
				?>
			</select>
		</div>
		<?php if ($arEditorSettings['link']['types']['external']['options']['nofollow']) { ?>
			<div class="field">
				<label for="arnofollow"><?php echo $ARnls["ariadne:editor:nofollow"]; ?></label>
				<input type="checkbox" name="arnofollow" id="arnofollow" value="1"<?php if ($arnofollow) { echo " checked"; } ?>>
			</div>
		<?php } ?>
</fieldset>
<?php
	}
?>
