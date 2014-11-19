<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$result = array(
			"href" => "",
			"name" => ""
		);
		?>
		<script type="text/javascript">
			if (window.opener) {
				if (window.opener.muze && window.opener.muze.dialog && window.opener.muze.dialog.hasCallback( window.name, 'submit') ) {
					window.opener.muze.dialog.callback( window.name, 'submit', <?php echo json_encode($result); ?> );
				} else if (window.opener.callback) {
					window.opener.callback(<?php echo json_encode($result); ?>);
				}
			}
			window.close();
		</script>
		<?php
	}
?>
