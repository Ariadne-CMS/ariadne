<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckSilent('edit') && $this->CheckConfig()) {
		?>
		<script>
			top.close();
		</script>
		<?php
	}
?>
