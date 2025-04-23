<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
?>

	<fieldset id="data">
		<legend><?php echo $ARnls["language"]; ?></legend>
		<div class="left">
			<div class="field">
				<input type="radio" class="inputradio" id="inherit" name="inherit" value="1" <?php
					if( !($this->data->config->nlsconfig->list??null)) { // set for this object
						echo "checked";
					}
				?>>
				<label for="inherit" class="required"><?php echo $ARnls["inherit"]; ?></label>
			</div>
			<div class="field">
				<label for="inheritavailable" class="ontop"><?php echo $ARnls["available"]; ?></label>
				<select id="inheritavailable" name="inheritavailable" class="multiselect" disabled multiple>
				<?php
					$config=$ARConfig->cache[$this->parent];
					foreach( $config->nls->list as $key => $value ) {
						echo "<option value=\"$key\">[$key] $value</option>\n";
					}
				?>
				</select>
			</div>
			<div class="field">
				<label for="inheritdefault" class="ontop"><?php echo $ARnls["default"]; ?></label>
				<select id="inheritdefault" name="inheritdefault" disabled>
				<?php
					$config=$ARConfig->cache[$this->parent];
					foreach( $config->nls->list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $key == $config->nls->default ) { echo "selected"; }
						echo ">[$key] $value</option>\n";
					}
				?>
				</select>
			</div>
		</div>
		<div class="right">
			<div class="field">
				<input type="radio" class="inputradio" id="use" name="inherit" value="0" <?php
					if( $this->data->config->nlsconfig->list??null ) { // set for this object
						echo "checked";
					}
				?>>
				<label for="use" class="required"><?php echo $ARnls["use"]; ?> : </label>
			</div>
			<div class="field">
				<label for="available" class="ontop"><?php echo $ARnls["available"]; ?></label>
				<select id="available" name="available[]" class="multiselect" multiple>
				<?php
					$selected = [];
					if ($data->config->nlsconfig->list ?? null) { // acquired from parents
						$selected=$data->config->nlsconfig->list;
					}
					foreach( $AR->nls->list as $key => $value ) {
						if ($selected[$key] ?? null) {
							echo "<option value=\"$key\" selected>[$key] $value</option>\n";
						} else {
							echo "<option value=\"$key\">[$key] $value</option>\n";
						}
					}
				?>
				</select>
			</div>
			<div class="field">
				<label for="default" class="ontop"><?php echo $ARnls["default"]; ?></label>
				<select id="default" name="default">
				<?php
					$selected = $config->nls->default ?? null;
					if ($data->config->nlsconfig->default ?? null) {
						$selected=$data->config->nlsconfig->default;
					}
					foreach( $AR->nls->list as $key => $value ) {
						if ($selected==$key) {
							echo "<option value=\"$key\" selected>[$key] $value</option>\n";
						} else {
							echo "<option value=\"$key\">[$key] $value</option>\n";
						}
					}
				?>
				</select>
			</div>
		</div>
	</fieldset>

<?php
	}
?>
