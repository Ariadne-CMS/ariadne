<?php
//	include_once($this->store->get_config("code")."modules/mod_grant.php");
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);      
		exit;
	}
	include_once("dialog.grants.logic.php");

	$data = $this->getdata('data');

	// save the grants for the given path. Modify the type specific grants first though.
	foreach ($data as $path => $users) {
		foreach ($users as $user => $grants) {
			if ($grants['grants']['grantsstring']) {
				$this->get($path, "system.save.grants.phtml", array("path" => $user, "newgrants" => $data[$path][$user]['grants']['grantsstring']));
			} else {
				foreach ($grants['grants']['array'] as $grant => $value) {
					if ($value == 8) { // FIXME: use ARGRANTBYTYPE
						if (is_array($grants['grants']['bytype']) && is_array($grants['grants']['bytype'][$grant])) {
							$data[$path][$user]['grants']['array'][$grant] = $grants['grants']['bytype'][$grant];
							unset($data[$path][$user]['grants']['bytype']);
						} else {
							unset($data[$path][$user]['grants']['array'][$grant]);
						}
					}
					if ($value == 0) {
						unset($data[$path][$user]['grants']['array'][$grant]);
					}
				}
				$this->get($path, "system.save.grantsarray.php", array("userpath" => $user, "grants" => $data[$path][$user]['grants']['array']));
			}
		}
	}

	if (!$this->error) {
		$arReturnPage = $this->getdata('arReturnPage');
		ldRedirect($arReturnPage);
		?>
			<a href="<?php echo $arReturnPage; ?>">Continue</a>
			<script type="text/javascript">
				window.opener.muze.ariadne.explore.view('<?php echo $this->path;?>');
				window.close();
			</script>
		<?php
	} else {
		$this->call("show.error.phtml");
	}
?>