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

		$mimetype = $this->getdata("mimetype", $selectednls);
		if(!$mimetype) {
			$mimetype = $this->getdata("file_type", $selectednls);
		}

		$file_size = $this->getdata("file_size", $selectednls); // Filesize of the uploaded file
		if (!$file_size && $this->ExistsFile("file", $selectednls)) {
			$file_size = $this->getdata("filesize", $selectednls); // Filesize of the file that is in Ariadne.
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
	<?php if (!$arNewType) { ?>
	<div class="field">
		<label for="summary"><?php echo $ARnls["summary"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<textarea name="<?php echo $selectednls."[summary]"; ?>" class="inputbox" rows="5" cols="42"><?php
			echo $this->showdata("summary", $selectednls);
		?></textarea>
	</div>
	<div class="field">
		<label for="file"><?php echo $ARnls["file"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<input id="file" type="file" name="<?php echo $selectednls."[file]"; ?>" class="inputline" onchange="if (document.getElementById('file_uploaded')) {document.getElementById('file_uploaded').style.display='none'};">
		<?php if ($this->getdata("file_size", $selectednls)) { ?>
			<div id="file_uploaded" class="file_uploaded"><?php echo $ARnls['ariadne:file_uploaded']; ?>: <?php echo $this->make_filesize($this->getdata("file_size", $selectednls)); ?></div>
			<script type="text/javascript">
				if (document.getElementById("file").value) {
					document.getElementById('file_uploaded').style.display='none';
				}
			</script>
		<?php } ?>
	</div>
	<?php } ?>
<?php	if ($file_size) { 
		$checked = '';
		if ($this->getdata("delete", $selectednls)) {
			$checked = "checked ";
		}
	?>
	<div class="field checkbox">
		<input type="hidden" name="<?php echo $selectednls . "[delete]"; ?>" value="0">
		<input id="delete" type="checkbox" name="<?php echo $selectednls . "[delete]"; ?>" class="checkbox" value="1" <?php echo $checked; ?>>
		<label for="delete"><?php echo $ARnls["ariadne:remove_file"]; ?> (<?php echo $ARnls['size']; ?>: <?php echo $this->make_filesize($file_size); ?>)</label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
	</div>
<?php	} ?>
	<div class="field">
		<label for="mimetype"><?php echo $ARnls["ariadne:mimetype"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<input id="mimetype" type="text" name="<?php echo $selectednls."[mimetype]"; ?>" 
			value="<?php echo htmlspecialchars($mimetype); ?>" class="inputline">
	</div>
</fieldset>

<?php } ?>