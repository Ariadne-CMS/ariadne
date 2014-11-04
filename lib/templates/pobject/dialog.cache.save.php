<?php
	ldDisablePostProcessing();
	$ARCurrent->nolangcheck=true;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$this->call("system.save.cache.phtml", $arCallArgs);
		if (!$this->error) {
			?>
			<script>
				window.opener.muze.ariadne.explore.sidebar.view('<?php echo $this->path;?>');
				window.close();
			</script>
			<?php
		} else {
			$this->call("show.error.phtml");
		}
	}
?>