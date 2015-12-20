<?php
	$ARCurrent->nolangcheck=true;
        if ($this->CheckLogin("layout") && $this->CheckConfig()) {

        	$svninfo = current($this->get($this->parent, "system.svn.info.php"));

			$repository = "";
			if( $svninfo && $svninfo["url"] ) {
				$repository = $svninfo["url"]."/".basename($this->path);
			}

?>
		<fieldset id="data">
			<legend><?php echo $ARnls['ariadne:svn:repository_information']; ?></legend>
			<div class="field">
				<label for="repository" class="required"><?php echo $ARnls['ariadne:svn:repository']; ?></label>
				<input id="repository" type="text" name="repository" value="<?php echo $repository; ?>" class="inputline wgWizAutoFocus">
			</div>
			<div class="field">
				<label for="username" class="required"><?php echo $ARnls['ariadne:svn:username']; ?></label>
				<input id="username" type="text" name="username" value="" class="inputline">
			</div>
			<div class="field">
				<label for="password" class="required"><?php echo $ARnls['ariadne:svn:password']; ?></label>
				<input id="password" type="password" name="password" value="" class="inputline">
			</div>
			<div class="field">
				<label for="message" class="required"><?php echo $ARnls['ariadne:svn:message']; ?></label>
				<textarea id="message" name="message" class="inputbox" rows="5" cols="42"></textarea>
			</div>
		</fieldset>
<?php
	}
?>
