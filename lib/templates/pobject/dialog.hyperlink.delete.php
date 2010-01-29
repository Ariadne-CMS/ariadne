<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		?>
		<script type="text/javascript">
			if (window.opener && window.opener.callback) {
				window.opener.callback({});
			}
			window.close();
		</script>
		<?php
	}
?>