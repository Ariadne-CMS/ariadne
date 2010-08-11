<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$type = $this->getvar("type");
		if (!$type) {
			$type = $this->type;
		}
		$vtype = $this->getvar("vtype");
		if (!$vtype) {
			$vtype = $this->vtype;
		}
		$this->call('typetree.ini');
		asort($ARCurrent->arTypeNames);
?>
<fieldset id="data" class="mogrify">
	<legend><?php echo $ARnls["ariadne:mogrify"]; ?></legend>
	<div class="field">
		<label for="target" class="required" style="float: left; width: 65px; margin-top: 5px;"><?php echo $ARnls["ariadne:type"]; ?></label>
		<select class="selectline" name="type">
		<?php
			foreach ( $ARCurrent->arTypeNames as $typeValue => $typeName ) {
				echo '<option value="'.$typeValue.'"';
				if ($typeValue==$type) {
					echo ' selected';
				}
				echo '>'.$typeName.' ( '.$typeValue.' ) </option>'."\n";
			}
		?>
		</select>
	</div>
</fieldset>
<?php	} 
?>