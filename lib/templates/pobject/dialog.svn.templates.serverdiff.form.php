<?php
	$ARCurrent->nolangcheck=true;
        if ($this->CheckLogin("layout") && $this->CheckConfig()) {
?>
		<fieldset id="data">
			<legend><?php echo $ARnls['ariadne:svn:repository_information']; ?></legend>
			<div class="field">
				<label for="username" class="required"><?php echo $ARnls['ariadne:svn:username']; ?></label>
				<input id="username" type="text" name="username" value="" class="inputline">
			</div>
			<div class="field">
				<label for="password" class="required"><?php echo $ARnls['ariadne:svn:password']; ?></label>
				<input id="password" type="password" name="password" value="" class="inputline">
			</div>
			<div class="field">
				<label for="revision" class="required"><?php echo $ARnls['ariadne:svn:revision']; ?></label>
				<input id="revision" type="text" name="revision" value="" class="inputline">
			</div>
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
