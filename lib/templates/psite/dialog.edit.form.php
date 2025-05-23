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


		$urlList = $this->getdata('urlList', $selectednls);
		if (!is_array($urlList) || !count($urlList)) {
			$urlList = array( $this->getdata('url', $selectednls) );
		}
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
		<label for="name" class="required"><?php echo $ARnls["url"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<?php
			$snippetDef = array(
				'url' => array(
						'name' => $selectednls . '[urlList]',
						'type' => 'fieldlist',
						'label' => false,
						'class' => 'inputline',
						'value' => $urlList
				)
			);
			$formSnippet = ar('html')->parse( (string)ar('html')->form( $snippetDef, null, null ) );
			$snippet = $formSnippet->getElementsByTagName('fieldset');
			echo (string)$snippet;
		?>
	</div>
	<?php
		if (getenv("ARIADNE_WORKSPACE") && workspace::enabled($this->path)) {
	?>
	<div class="field">
		<label for="name" class="required"><?php echo $ARnls["ariadne:workspace:url"] . ": "; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<input id="name" type="text" name="<?php echo $selectednls."[workspaceurl]"; ?>"
			value="<?php
					if (!$workspaceurl = $this->getdata("workspaceurl", $selectednls)) {
						if (!($this->arIsNewObject??null) && ($selectednls==$this->data->nls->default)) {
							$url = $this->getdata("workspaceurl", "none");
						}
					}
					echo htmlspecialchars( $workspaceurl??'', ENT_QUOTES, 'UTF-8');
				?>" class="inputline">
	</div>
	<?php
		}
	?>
	<?php if (!($arNewType??null)) { ?>
	<div class="field">
		<label for="summary"><?php echo $ARnls["summary"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<textarea name="<?php echo $selectednls."[summary]"; ?>" class="inputbox"><?php
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
	<?php } ?>
	<?php
		if ( $arNewType??null ) {
			$this->call('dialog.edit.form.scaffolds.php', $this->getvar('arCallArgs'));
		}
	?>
</fieldset>

<?php } ?>
