<?php
	$ARCurrent->nolangcheck=true;
	if( $this->CheckLogin("edit") && $this->CheckConfig() ) {
		$priority = $this->getvar("priority");

		if (!isset($priority)) {
			$priority = $this->priority;
		} else {
			$priority = (int)$priority;
		}
?>
	<fieldset id="data">
			<legend><?php echo $ARnls["priority"]; ?></legend>
			<div class="field">
				<label for="priority" class="required"><?php echo $ARnls["priority"]; ?></label>
				<input id="priority" type="text" name="priority" value="<?php echo $priority; ?>" class="inputline wgWizAutoFocus">
			</div>
	</fieldset>
<?php
	}
?>
