<?php
	/******************************************************************
	
	******************************************************************/
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);      
		exit;
	}

	$this->call("system.save.custom.phtml", $arCallArgs);
	if (!$this->error) {
		if ($arReturnPage) {
			ldHeader("Location: ".$this->store->get_config("root").$arReturnPage);
		} else {
		?>
			<script>
				if (window.opener) {
					window.opener.muze.ariadne.explore.sidebar.view('<?php echo $this->path;?>');
					window.close();
				}
			</script>
		<?php
		}
	} else {
		echo "<font color='red'>$this->error</font>";
	}
?>