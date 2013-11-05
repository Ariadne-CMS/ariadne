<?php
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);      
		exit;
	}
	$unlocks = $this->getdata("unlock");
	$arReturnPage = $this->getdata("arReturnPage");
	$locks=$this->store->mod_lock->get_locks($this->data->login);
	if (is_array($unlocks)) {
		foreach ($unlocks as $path => $checked) {
			$this->store->mod_lock->unlock($this->data->login, $path);
		}
	}
	if ($arReturnPage) {
	?>
		<script type="text/javascript">
			document.location.href = "<?php echo $arReturnPage; ?>";
		</script>
	<?php
		// ldRedirect($arReturnPage);
	}
?>