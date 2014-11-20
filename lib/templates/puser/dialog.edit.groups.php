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

		$login = $this->data->login;
		if (!$login) {
			$login = $this->getdata("arNewFilename", "none");
		}

		$selected = array();
		$groups = $this->getdata("groups", "none");
		if (is_array($groups)) {
			foreach ($groups as $key => $value) {
				if (substr($value, 0, 1) == "/") {
					$selected[$value] = $value;
				} else {
					$selected[$key] = $value;
				}
			}
		}

		$userConfig = $this->loadUserConfig();
		$authconfig = $userConfig['authentication'];

?>
<fieldset id="groups">
	<legend><?php echo sprintf($ARnls["groupmembership"], $login); ?></legend>
	<div class="field">
		<select multiple input id="groups" size="10" name="groups[]" class="selectline">
		<?php
			foreach ($authconfig['groupdirs'] as $groupdir) {
				$this->find(
					$groupdir,
					"object.implements = 'pgroup' and login.value != 'owner' order by name.value",
					"show.option.multiple.phtml",
					Array(
						"selected"	=> $selected,
						"grant"		=> "edit"
					),
					0, 0
				);
			}
		?>
		</select>
	</div>
</fieldset>

<?php } ?>
