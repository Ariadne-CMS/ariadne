<?php
  /******************************************************************

   no result.

  ******************************************************************/
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read")) {
	?>
	<fieldset>
		<legend><?php echo $ARnls["ariadne:wysiwyg_editor"]; ?></legend>
		<div class="field select">
			<select name="editor">
					<?php $editor=$this->getdata("editor"); ?>
					<option value="wysiwyg"><?php echo $ARnls["wysiwyg"]; ?></option>
					<option value="toolbar" <?php if ($editor=="toolbar") { echo "selected"; } ?>><?php echo $ARnls["toolbar_editor"]; ?></option>
			</select>
		</div>
	</fieldset>
	<fieldset>
		<legend><?php echo $ARnls["ariadne:template_editor"]; ?></legend>
		<div class="field select">
			<select name="template_editor">
					<?php $template_editor=$this->getdata("template_editor"); ?>
					<option value="textarea"><?php echo $ARnls["ariadne:templateeditor:textarea"]; ?></option>
					<option value="ace" <?php if ($template_editor=="ace") { echo "selected"; } ?>><?php echo $ARnls["ariadne:templateeditor:ace"]; ?></option>
			</select>
		</div>
	</fieldset>
<?php
	}
?>
