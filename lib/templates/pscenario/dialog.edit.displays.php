<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
?>
<script language="javascript">
<!--
	function show_display_values() {
		sbox = document.wgWizForm["display-select"];
		if ( sbox.selectedIndex != -1 ) {
			display = sbox.options[ sbox.selectedIndex ].value;
			if (display) {
				if (! (document.wgWizForm["display-start"].value = document.wgWizForm[ "display[" + display + "][start]" ].value)) {
					// default to zero
					document.wgWizForm["display-start"].value = 0;
				}
				if (! (document.wgWizForm["display-end"].value = document.wgWizForm[ "display[" + display + "][end]" ].value)) {
					// default to zero
					document.wgWizForm["display-end"].value = 0;
				}
			}
		}
	}

	function commit_display_values() {
		sbox = document.wgWizForm["display-select"];

		if ( sbox.selectedIndex != -1 ) {
			display = sbox.options[ sbox.selectedIndex ].value;
			if (display) {
				document.wgWizForm[ "display[" + display + "][start]" ].value = document.wgWizForm["display-start"].value;
				document.wgWizForm[ "display[" + display + "][end]" ].value = document.wgWizForm["display-end"].value;
			}
		}
	}
	YAHOO.util.Event.onDOMReady(show_display_values);

//-->
</script>
<fieldset id="priority">
	<legend><?php echo $ARnls["priority"]; ?></legend>
	<div class="field">
		<label for="priority"><?php echo $ARnls["priority"]; ?></label>
		<input type="text" name="priority" maxlength="50" id="priority"
			value="<?php $this->showdata("priority","none"); ?>" class="inputline">
	</div>
</fieldset>
<fieldset id="display">
	<legend><?php echo $ARnls["display"]; ?></legend>
	<div class="field">
		<label for="display-select"><?php echo $ARnls["id"]; ?></label>
		<select id="display-select" name="display-select" onChange="show_display_values();">
		<?php
			$displays=$this->ls("/system/newspaper/displays/","system.get.value.phtml");
			if( is_array($displays ) ) {
				foreach( $displays as $key => $val ) {
					echo "<option value=\"$val\">$val</option>";
				}
			}
		?>
		</select>
		<?php
			// now setup the hidden form values
			if (is_array($displays)) {
				$ddata=$this->getdata("display", "none");
				foreach( $displays as $key => $val ) {
					?>
						<input type="hidden" name="display[<?php echo $val; ?>][start]" value="<?php echo (int) $ddata[$val]["start"]; ?>">
						<input type="hidden" name="display[<?php echo $val; ?>][end]" value="<?php echo (int) $ddata[$val]["end"]; ?>">
					<?php
				}
			}
		?>
	</div>
	<div class="field">
		<label for="display-start"><?php echo $ARnls["start"]; ?></label>
		<input type="text" name="display-start" maxlength="50" id="display-start" onChange="commit_display_values();" value="" class="inputline">
	</div>
	<div class="field">
		<label for="display-end"><?php echo $ARnls["end"]; ?></label>
		<input type="text" name="display-end" maxlength="50" id="display-end" onChange="commit_display_values();" value="" class="inputline">
	</div>
</fieldset>
<?php
	}
?>
