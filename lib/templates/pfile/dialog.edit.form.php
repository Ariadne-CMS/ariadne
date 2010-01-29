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
?>
<fieldset id="data">
	<legend><?php echo $ARnls["data"]; ?></legend>
	<div class="field">
		<label for="name" class="required"><?php echo $ARnls["name"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<input id="name" type="text" name="<?php echo $selectednls."[name]"; ?>" 
			value="<?php $this->showdata("name", $selectednls); ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="mimetype"><?php echo $ARnls["ariadne:mimetype"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<input id="mimetype" type="text" name="<?php echo $selectednls."[mimetype]"; ?>" 
			value="<?php $this->showdata("mimetype", $selectednls); ?>" class="inputline">
	</div>
	<div class="field">
		<label for="summary"><?php echo $ARnls["summary"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<textarea name="<?php echo $selectednls."[summary]"; ?>" class="inputbox" rows="5" cols="42"><?php
			echo ereg_replace("&","&amp;",$this->getdata("summary", $selectednls));
		?></textarea>
	</div>
	<div class="field">
		<label for="file"><?php echo $ARnls["file"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<input id="file" type="file" name="<?php echo $selectednls."[file]"; ?>" class="inputline">
	</div>
</fieldset>

<?php } ?>
