<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) {
			$arLanguage=$ARConfig->nls->default;
		}
		$selectednls=$arLanguage;
		$selectedlanguage=$AR->nls->list[$arLanguage];

		$flagurl = $AR->dir->images."nls/small/$selectednls.gif";
?>
<fieldset id="name">
	<legend><?php echo $ARnls["name"]; ?></legend>
	<div class="field">
		<label for="firstname"><?php echo $ARnls["first"]; ?></label>
		<input id="firstname" type="text" name="firstname" class="inputline wgWizAutoFocus" value="<?php $this->showdata("firstname", "none"); ?>">
	</div>
	<div class="field">
		<label for="middlename"><?php echo $ARnls["middle"]; ?></label>
		<input id="middlename" type="text" name="middlename" class="inputline" value="<?php $this->showdata("middlename", "none"); ?>">
	</div>
	<div class="field">
		<label for="lastname"><?php echo $ARnls["last"]; ?></label>
		<input id="lastname" type="text" name="lastname" class="inputline" value="<?php $this->showdata("lastname", "none"); ?>">
	</div>
</fieldset>
<fieldset id="comments">
	<legend><?php echo $ARnls["comments"]; ?></legend>
	<div class="field">
		<label for="summary"><?php echo $ARnls["summary"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<textarea id="summary" name="<?php echo $selectednls."[summary]"; ?>" class="inputbox" rows="5" cols="42"><?php
			echo $this->showdata("summary", $selectednls);
		?></textarea>
	</div>
	<?php
		if ( $arNewType??null ) {
			$this->call('dialog.edit.form.scaffolds.php', $this->getvar('arCallArgs'));
		}
	?>
</fieldset>
<?php
	}
?>
