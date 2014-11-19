<?php
	$url = $this->store->get_config("root").$this->path."dialog.templates.php";
	$search = $this->getvar('search');
	if ($search) {
		$url .= "?search=" . RawUrlEncode($search);
	}
?><script type="text/javascript">
<!--
  top.window.location.href = '<?php echo $url; ?>';
// -->
</script>
