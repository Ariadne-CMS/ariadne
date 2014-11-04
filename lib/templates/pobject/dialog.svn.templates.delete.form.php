<?php
	$ARCurrent->nolangcheck=true;
        if ($this->CheckLogin("layout") && $this->CheckConfig()) {
?>
		<fieldset id="data">
			<legend><?php echo $ARnls['ariadne:svn:delete']; ?></legend>
			<div class="field"><?php echo $ARnls['ariadne:svn:deleteconfirm']; ?></div>
		</fieldset>
<?php
		$type = $this->getvar("type");
		$language = $this->getvar("language");
		$function = $this->getvar("function");

		if( $type && $language && $function ) {
?>
		<input id="type" type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
		<input id="language" type="hidden" name="language" value="<?php echo htmlspecialchars($language); ?>">
		<input id="function" type="hidden" name="function" value="<?php echo htmlspecialchars($function); ?>">
<?php
		}
	}
?>
