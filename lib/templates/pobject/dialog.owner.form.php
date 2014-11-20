<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("config") && $this->CheckConfig()) {
		$this->data = current($this->get($this->path, "system.get.data.phtml"));
		$query = "object.implements='puser' and login.value='".AddSlashes($this->data->config->owner)."'";
		$wgBrowsePath = current($this->find('/system/users/', $query, 'system.get.path.phtml'));
		if (!$wgBrowsePath) {
			$wgBrowsePath = '/system/users/';
		}
		$wgBrowseRoot = '/system/users/';

		$arConfig = $this->loadUserConfig();
		$extraroots = '';
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

?>
<script type="text/javascript">
	function callback(path) {
		document.getElementById("owner").value = path;
	}
</script>
<fieldset id="data" class="browse">
		<legend><?php echo $ARnls["path"]; ?></legend>
		<div class="field">
			<label for="owner" class="required"><?php echo $ARnls["owner"]; ?></label>
			<input type="text" id="owner" name="owner" value="<?php echo $wgBrowsePath; ?>" class="inputline wgWizAutoFocus">
			<input class="button" type="button" value="<?php echo $ARnls['browse']; ?>" title="<?php echo $ARnls['browse']; ?>" onclick='callbacktarget="extrauser"; window.open("<?php echo $this->make_ariadne_url($wgBrowseRoot); ?>" + "dialog.browse.php<?php if ($extraroots) { echo "?$extraroots"; }?>", "browse", "height=480,width=750"); return false;'>
		</div>
		<div class="field radio">
			<input type="radio" id="normal" name="behaviour" value="0" checked>
			<label for="normal"><?php echo $ARnls['ariadne:currentobjectonly']; ?></label>
		</div>
		<div class="field radio">
			<input type="radio" id="recursive" name="behaviour" value="recursive">
			<label for="recursive"><?php echo $ARnls['ariadne:currentandchildren']; ?></label>
		</div>
		<div class="field radio">
			<input type="radio" id="childrenonly" name="behaviour" value="childrenonly">
			<label for="childrenonly"><?php echo $ARnls['ariadne:childrenonly']; ?></label>
		</div>
</fieldset>
<?php
	}
?>
