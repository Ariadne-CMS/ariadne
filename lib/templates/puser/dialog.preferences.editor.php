<?php
  /******************************************************************

   no result.

  ******************************************************************/
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read")) {
	?>
	<fieldset>
		<legend><?php echo $ARnls["editor"]; ?></legend>
		<div class="field select">
			<select name="editor">
					<?php $editor=$this->getdata("editor"); ?>
					<option value="wysiwyg"><?php echo $ARnls["wysiwyg"]; ?></option>
					<option value="toolbar" <?php if ($editor=="toolbar") { echo "selected"; } ?>><?php echo $ARnls["toolbar_editor"]; ?></option>
			</select>
		</div>
	</fieldset>
<?php
	}
?>