<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) {
		 $arLanguage=$ARConfig->nls->default;
		}
		$selectednls=$arLanguage;
		$selectedlanguage=$ARConfig->nls->list[$arLanguage];
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
				<label for="url"><?php echo $ARnls["url"]; ?></label>
				<input class="inputline" type="text" name="value" id="url"
					value="<?php $this->showdata("value","none"); ?>">
			</div>
			<div class="field">
				<label for="summary"><?php echo $ARnls["summary"]; ?></label>
				<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
				<textarea id="summary" name="<?php echo $selectednls."[summary]"; ?>" class="inputbox" rows="5" cols="42"><?php
					echo $this->showdata("summary", $selectednls);
				?></textarea>
			</div>
		</fieldset>
<?php
	}
?>
