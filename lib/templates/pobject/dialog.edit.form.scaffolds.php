<?php
	$scaffoldConfig = ar::acquire('settings.scaffolds');
	if ( $scaffoldConfig ) {
		if ( !is_array($scaffoldConfig) ) {
			$scaffoldConfig = array($scaffoldConfig);
		}
?>
<div class="field">
	<label for="scaffold"><?php echo $ARnls["ariadne:scaffold"]; ?></label>
	<select id="scaffold" type="text" name="scaffold" class="selectline">
		<option value=""><?php echo $ARnls["ariadne:noscaffold"]; ?></option>
		<?php
			foreach ($scaffoldConfig as $scaffold) {
				$this->find(
					$scaffold.$this->type,
					"object.type = '".$this->type."' and object.parent='".$scaffold.$this->type."/'",
					"show.option.phtml",
					array(
						"selected" => $this->getdata("scaffold", "none")
					)
				);
			}
		?>
	</select>
</div>
<?php
	}
?>