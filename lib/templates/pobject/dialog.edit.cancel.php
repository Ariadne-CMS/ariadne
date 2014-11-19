<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent('edit') && $this->CheckConfig()) {
		$this->unlock('O');
		?>
		<script>
			top.window.close();
		</script>
		<?php
	}
?>
