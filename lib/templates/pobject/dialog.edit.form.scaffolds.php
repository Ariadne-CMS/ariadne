<?php
	$arLanguage = ar::getvar('arLanguage');
	if ( !$arLanguage || $arLanguage != $ARConfig->nls->default ) {
		return;
	}
	$scaffoldConfig = ar::acquire('settings.scaffolds');
	if ( $scaffoldConfig ) {
		if ( !is_array($scaffoldConfig) ) {
			$scaffoldConfig = array($scaffoldConfig);
		}
		$count = 0;
		foreach ($scaffoldConfig as $scaffold) {
			$query = "object.type = '".$this->type."' and object.parent='".$scaffold.$this->type."/' and name.nls='$arLanguage'";
			$count += ar::get($scaffold.$this->type)->find($query)->count();
		}
		if ($count) {
?>
<div class="field">
	<label for="scaffold"><?php echo $ARnls["ariadne:scaffold"]; ?></label>
	<select id="scaffold" type="text" name="scaffold" class="selectline">
		<option value=""><?php echo $ARnls["ariadne:noscaffold"]; ?></option>
		<?php
			foreach ($scaffoldConfig as $scaffold) {
				$query = "object.type = '".$this->type."' and object.parent='".$scaffold.$this->type."/' and name.nls='$arLanguage'";
				$this->find(
					$scaffold.$this->type,
					$query,
					"show.option.phtml",
					array(
						"selected" => $this->getdata("scaffold", "none"),
						"arLanguage" => $arLanguage
					)
				);
			}
		?>
	</select>
</div>
<?php
		}
	}
?>