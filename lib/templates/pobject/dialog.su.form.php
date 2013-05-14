<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("admin") && $this->CheckConfig()) {
		$arConfig = $this->loadUserConfig();
		foreach($arConfig['authentication'] as $grouptype => $authdirs) {
			if (in_array($wgBrowseRoot, $arConfig['authentication'][$grouptype])) {
				foreach ($arConfig['authentication'][$grouptype] as $authpath) {
					if ($authpath != $wgBrowseRoot) {
						$extraroots .= "extraroots[]=$authpath&";
					}
				}
			}
		}
		if ($extraroots) {
			$extraroots = substr($extraroots, 0, -1);
		}

		$target = $this->getvar("target");
		$target = false;

		if (!$target) {
			$target = $arConfig['authentication']['userdirs'][0];
		}
?>
<script type="text/javascript">
	function callback(path) {
		document.getElementById("target").value = path;
	}
</script>
<fieldset id="data" class="browse">
		<legend><?php echo $ARnls["path"]; ?></legend>
		<div class="field">
			<label for="target" class="required"><?php echo $ARnls["target"]; ?></label>
			<input id="target" type="text" name="target" value="<?php echo $target; ?>" class="inputline wgWizAutoFocus">
			<input class="button" type="button" value="<?php echo $ARnls['browse']; ?>" title="<?php echo $ARnls['browse']; ?>" onclick='window.open("<?php echo $this->make_ariadne_url('/'); ?>" + document.getElementById("target").value + "dialog.browse.php<?php echo $extraroots ? "?" . $extraroots : ""; ?>", "browse", "height=480,width=750"); return false;'>
			<div class="clear"></div>
		</div>
</fieldset>
<?php	} 
?>