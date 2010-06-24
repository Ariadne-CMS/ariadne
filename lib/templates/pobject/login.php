<?php
	$ARCurrent->allnls = true;
	$ARCurrent->nolangcheck = true;
	
	if ($this->CheckConfig()) {
		// store the get and post vars again, these will be restored by the loader after the redirect or link click.
		if (!$ARCurrent->session->get("oldArCallArgs", 1)) {
			$ARCurrent->session->put("oldGET", $_GET, 1);
			$ARCurrent->session->put("oldPOST", $_POST, 1);
			$ARCurrent->session->put("oldArCallArgs", $arCallArgs, 1);
			$ARCurrent->session->save(0);
		}
		ldRedirect($this->make_local_url($arReturnPath).$arReturnTemplate);
?>
			<a href="<?php echo $this->make_local_url($arReturnPath).$arReturnTemplate; ?>">Continue</a>
<?php
	}
?>
