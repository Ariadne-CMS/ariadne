<?php
	$ARCurrent->nolangcheck=true;
        if ($this->CheckLogin("layout") && $this->CheckConfig()) {

			$svninfo = current($this->get($this->parent, "system.svn.info.php"));

			$repository = "";
			if( is_array($svninfo) && $svninfo["url"] ) {
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
				<label for="revision" class="required"><?php echo $ARnls['ariadne:svn:revision']; ?></label>
				<input id="revision" type="text" name="revision" value="" class="inputline">
			</div>
			<div class="field">
				<label for="checkunder"><?php echo $ARnls['ariadne:svn:checkunder']; ?></label>
				<input id="checkunder" type="checkbox" name="checkunder" value="1">
			</div>
		</fieldset>
<?php
	}
?>
