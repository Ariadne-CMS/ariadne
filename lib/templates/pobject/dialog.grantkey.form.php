<?php
	$ARCurrent->nolangcheck=true;
	if( $this->CheckLogin("config") && $this->CheckConfig() ) {
		$grants = $this->getvar("grants");
		$grantkey = "";
		if( $grants ) {
			$grantkey = $this->sgKey($grants);
		}
		if ($AR->sgSalt) {

?>
	<fieldset id="data">
			<legend><?php echo $ARnls["ariadne:grantkey"]; ?></legend>
			<div class="field">
				<label for="grants" class="required"><?php echo $ARnls["grants"]; ?></label>
				<textarea id="grants" name="grants" class="inputbox<?php if( !$grantkey ) echo " wgWizAutoFocus"; ?>" rows="5" cols="42"><?php
					echo htmlspecialchars($this->getvar("grants")??'', ENT_QUOTES, 'UTF-8');
				?></textarea>
			</div>
			<div class="field">
				<label for="grantkey"><?php echo $ARnls["ariadne:grantkey"]; ?></label>
				<input type="text" value="<?php echo $grantkey; ?>" id="grantkey" name="grantkey" class="inputline <?php if( $grantkey ) echo " wgWizAutoFocus wgWizAutoSelect"; ?>">
			</div>
	</fieldset>
<?php
		} else {
			echo $ARnls['err:nosalt'];
		}
	}
?>
