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
		</fieldset>
<?php
	}
?>
