<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) {
			$arLanguage=$ARConfig->nls->default;
		}
		$selectednls=$arLanguage;
		$selectedlanguage=$AR->nls->list[$arLanguage];

		$flagurl = $AR->dir->images."nls/small/$selectednls.gif";


		$setowner = $this->getvar("setowner");
?>
<fieldset id="preferences">
	<legend><?php echo $ARnls["preferences"]; ?></legend>
	<div class="field">
		<label for="language"><?php echo $ARnls["language"]; ?></label>
		<select id="language" name="language">
		<?php
			$language = $this->getdata("language", "none");
			if (!$language) {
				$language = $nls;
			}
			foreach ($AR->nls->list as $key => $value) {
				$selected = "";
				if ($key == $language) {
					$selected = " selected";
				}
				echo "<option value=\"$key\"$selected>$value</option>\n";
			}
		?>
		</select>
	</div>
	<div class="field">
		<label for="editor"><?php echo $ARnls["editor"]; ?></label>
		<select id="editor" name="editor">
		<?php
			$editor = $this->getdata("editor", "none");
			$editorList = array(
				"wysiwyg"	=> $ARnls["wysiwyg"],
				"toolbar"	=> $ARnls["toolbar_editor"],
			);
			foreach ($editorList as $key => $value) {
				$selected = "";
				if ($key == $editor) {
					$selected = " selected";
				}
				echo "<option value=\"$key\"$selected>$value</option>\n";
			}
		?>
		</select>
	</div>
</fieldset>
<fieldset id="contactInfo">
	<legend><?php echo $ARnls["contactinformation"]; ?></legend>
	<div class="field">
		<label for="email"><?php echo $ARnls["email"]; ?></label>
		<input id="email" type="text" name="email"
			value="<?php $this->showdata("email", "none"); ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="telephone"><?php echo $ARnls["telephone"]; ?></label>
		<input id="telephone" type="text" name="telephone"
			value="<?php $this->showdata("telephone", "none"); ?>" class="inputline wgWizAutoFocus">
	</div>
</fieldset>

<?php } ?>
