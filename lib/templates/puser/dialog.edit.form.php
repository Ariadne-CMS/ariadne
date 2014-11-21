<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) {
			$arLanguage=$ARConfig->nls->default;
		}
		$selectednls=$arLanguage;
		$selectedlanguage=$AR->nls->list[$arLanguage];

		$flagurl = $AR->dir->images."nls/small/$selectednls.gif";


		$setowner = $this->getvar("setowner");
		global $auth_config;
?>
<fieldset id="data">
	<legend><?php echo $ARnls["data"]; ?></legend>
	<div class="field">
		<label for="login" class="required"><?php echo $ARnls["login"]; ?></label>
		<input id="login" type="text" name="login"
			value="<?php $this->showdata("login", "none"); ?>" class="inputline">
	</div>
	<div class="field">
		<label for="name" class="required"><?php echo $ARnls["name"]; ?></label>
		<input id="name" type="text" name="name"
			value="<?php $this->showdata("name", "none"); ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="password" class="required"><?php echo $ARnls["password"]; ?>
		<?php if ($auth_config['expiry'] && $this->data->password_expiry) {
			echo "&nbsp;(" . $ARnls['expires'] . " " . date("d M Y, G:i", $this->data->password_expiry) .")";
		} ?>
		</label>
		<input id="password" type="password" name="newpass1"
			value="">
	</div>
	<div class="field">
		<label for="passwordAgain" class="required"><?php echo $ARnls["again"]; ?></label>
		<input id="passwordAgain" type="password" name="newpass2"
			value="">
	</div>
	<?php
		if($auth_config["expiry"] && $this->CheckSilent("config")) {
			if ($this->data->password_expiry) {
				$checked = "";
			} else {
				$checked = "checked ";
			}
	?>
	<div class="field checkbox">
		<input type="hidden" name="neverexpires" value="0">
		<input type="checkbox" <?php echo $checked; ?>name="neverexpires" id="neverexpires" value="1">
		<label for="neverexpires"><?php echo $ARnls['neverexpires']; ?></label>
	</div>
	<?php	} ?>
	<div class="field">
		<label for="profile"><?php echo $ARnls["profile"]; ?></label>
		<select id="profile" type="text" name="profile" class="selectline">
			<option value=""><?php echo $ARnls["noprofile"]; ?></option>
			<?php
				$userConfig  = $this->loadUserConfig();
				$profileDirs = (array)$userConfig["authentication"]["profiledirs"];
				array_unshift($profileDirs, "/system/profiles");
				foreach ($profileDirs as $profileDir) {
					$this->find(
						$profileDir,
						"object.implements = 'pprofile'",
						"show.option.phtml",
						array(
							"selected" => $this->getdata("profile", "none")
						)
					);
				}
			?>
		</select>
	</div>

	<?php

		$disabled = $this->getvar('disabled');
		if (!isset($disabled)) {
			$disabled = $this->data->config->disabled;
		}
		if (!in_array($this->data->login, array("admin", "public")) && $this->CheckSilent('config')) {
			if ($disabled) {
				$checked = "checked ";
			} else {
				$checked = "";
			}
	?>
	<div class="field checkbox">
		<input type="hidden" name="disabled" value="0">
		<input type="checkbox" <?php echo $checked; ?>name="disabled" id="disabled" value="1">
		<label for="disabled"><?php echo $ARnls['disableuser']; ?></label>
	</div>
	<?php	} ?>

	<div class="field">
		<label for="changeOwner" class="required"><?php echo $ARnls["change_owner_recursive"]; ?></label>
		<input id="changeOwner" type="radio" name="setowner"
			value="1" class="inputradio"<?php if ($setowner) echo " checked"; ?>><?php echo $ARnls["yes"]; ?>
		<input id="changeOwner" type="radio" name="setowner"
			value="0" class="inputradio"<?php if (!$setowner) echo " checked"; ?>><?php echo $ARnls["no"]; ?>
	</div>

</fieldset>

<?php } ?>
