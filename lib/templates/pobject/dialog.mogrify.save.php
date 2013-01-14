<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin('config') && $this->CheckConfig() && $this->can_mogrify() ) {
?>
	<div id="mogrify"><?php echo $ARnls["mogrifying"] . " " . $current_object_path; ?></div>
<?php
		$this->mogrify( $this->id, $this->getdata('type'));
		if ($this->error) {
			echo '<div class="error">'.$this->error.'</div>';
		}
?>	<div class="buttons">
	<div class="left">
	<input unselectable="on" type="submit" name="wgWizControl" class="wgWizControl" onClick="document.wgWizForm.wgWizAction.value='cancel';" value="<?php echo $ARnls['cancel']; ?>">
	</div>
	</div>
	<?php if (!$this->error) { ?>
		<script type="text/javascript">
			window.opener.muze.ariadne.explore.tree.refresh('<?php echo $this->path; ?>');
			window.opener.muze.ariadne.explore.view('<?php echo $this->path; ?>');
			window.close();
		</script>
	<?php } ?>
<?php
	}
?>