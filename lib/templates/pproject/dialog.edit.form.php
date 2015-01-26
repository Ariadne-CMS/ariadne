<?php
	$ARCurrent->nolangcheck=true;
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
	<?php if (!$arNewType) { ?>
	<div class="field">
		<label for="summary"><?php echo $ARnls["summary"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<textarea id="summary" name="<?php echo $selectednls."[summary]"; ?>" class="inputbox" rows="5" cols="42"><?php
			echo $this->showdata("summary", $selectednls);
		?></textarea>
	</div>
	<div class="field">
		<label for="value"><?php echo $ARnls["page"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<?php
			$wgHTMLEditName="page";
			$wgHTMLEditLanguage=$selectednls;
			$wgHTMLEditContent=$this->ParsePage($this->getdata("page",$selectednls), true, true);
			include($this->store->get_config("code")."widgets/htmledit/js.html");
			include($this->store->get_config("code")."widgets/htmledit/form.html");
		?>
	</div>
	<?php 
		} 
		if ( $arNewType ) {
			$scaffoldConfig = ar::acquire('settings.scaffolds');
			if ( $scaffoldConfig ) {
				if ( !is_array($scaffoldConfig) ) {
					$scaffoldConfig = array($scaffoldConfig);
				}
	?>
	<div class="field">
		<label for="scaffold"><?php echo $ARnls["ariadne:scaffold"]; ?></label>
		<select id="scaffold" type="text" name="scaffold" class="selectline">
			<option value=""><?php echo $ARnls["ariadne:noscaffold"]; ?></option>
			<?php
				foreach ($scaffoldConfig as $scaffold) {
					$this->find(
						$scaffold.'pproject/',
						"object.implements = 'pproject' and object.parent='".$scaffold."pproject/'",
						"show.option.phtml",
						array(
							"selected" => $this->getdata("scaffold", "none")
						)
					);
				}
			?>
		</select>
	</div>
	<?php
			}
		}
	?>
</fieldset>

<?php } ?>
