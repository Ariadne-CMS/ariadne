<?php
  /******************************************************************

   no result.

  ******************************************************************/
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read")) {
		$windowprefs = $this->getdata("windowprefs");
	?>
	<fieldset>
		<legend><?php echo $ARnls["openinnewwindow"]; ?></legend>
		<label><?php echo $ARnls["layout"]; ?></label>
		<div class="field radio">
			<input type="radio" name="windowprefs[edit_object_layout]" value="1" <?php if( $windowprefs["edit_object_layout"] ) echo "checked"; ?>>
			<?php echo $ARnls["yes"]; ?>
		</div>
		<div class="field radio">
			<input type="radio" name="windowprefs[edit_object_layout]" value="0" <?php if( !$windowprefs["edit_object_layout"] ) echo "checked"; ?>>
			<?php echo $ARnls["no"]; ?>
		</div>
		<label><?php echo $ARnls["grants"]; ?></label>
		<div class="field radio">
			<input type="radio" name="windowprefs[edit_object_grants]" value="1" <?php if( $windowprefs["edit_object_grants"] ) echo "checked"; ?>>
			<?php echo $ARnls["yes"]; ?>
		</div>
		<div class="field radio">
			<input type="radio" name="windowprefs[edit_object_grants]" value="0" <?php if( !$windowprefs["edit_object_grants"] ) echo "checked"; ?>>
			<?php echo $ARnls["no"]; ?>
		</div>
	</fieldset>
<?php
	}
?>
