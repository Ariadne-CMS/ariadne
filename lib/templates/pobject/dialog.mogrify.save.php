<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin('config') && $this->CheckConfig()) {
?>
	<div id="mogrify"><?php echo $ARnls["mogrifying"] . " " . $current_object_path; ?></div>
<?php
		$this->mogrify( $this->id, $this->getdata('type'), $this->getdata('vtype'));
		if ($this->error) {
			echo '<div class="error">'.$this->error.'</div>';
		}
?>	<div class="buttons">
	<div class="left">
	<input unselectable="on" type="submit" name="wgWizControl" class="wgWizControl" onClick="document.wgWizForm.wgWizAction.value='cancel';" value="<?php echo $ARnls['cancel']; ?>">
	</div>
	</div>
			<script type='text/javascript'>
				<?php if (!$this->error) { ?>
				window.close();
				<?php } ?>
			</script>
<?php
	}
?>