<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("edit") && $this->CheckConfig()) {
		$cacheconfig=$this->data->config->cacheconfig;
		$refresh=$this->getdata("refresh","none");
		$keepfor=$this->getdata("keepfor","none");
		if ($keepfor && $cacheconfig==1) {
			$cacheconfig=$keepfor;
		}
?>
		<fieldset id="cache">
			<legend><?php echo $ARnls["caching"]; ?></legend>
			<div class="field radio">
				<input id="inherit" type="radio" name="cacheconfig" value="0" <?php if (!$cacheconfig) { echo "checked"; } ?>>
				<label for="inherit"><?php echo $ARnls["inheritcache"]; ?></label>
			</div>
			<div class="field radio">
				<input id="refreshonrq" type="radio" name="cacheconfig" value="1" <?php if ($cacheconfig>0) { echo "checked"; } ?>>
				<label for="refreshonrq"><?php echo $ARnls["refreshonrq."]; ?></label>
			</div>
			<div class="field keepimage">
				<?php echo $ARnls["keepimage"]; ?>
				<input type="text" name="keepfor" size="2" maxlength="3" value="<?php if ($cacheconfig>0) { echo $cacheconfig; } else { echo "2"; } ?>">
				<?php echo $ARnls["hours"]; ?>.
			</div>
			<div class="field radio">
				<input id="refreshonch" type="radio" name="cacheconfig" value="-2" <?php if ($cacheconfig==-2) { echo "checked"; } ?>>
				<label for="refreshonch"><?php echo $ARnls["refreshonch."]; ?></label>
			</div>
			<div class="field radio">
				<input id="donotcache" type="radio" name="cacheconfig" value="-1" <?php if ($cacheconfig==-1) { echo "checked"; } ?>>
				<label for="donotcache"><?php echo $ARnls["donotcache"]; ?></label>
			</div>
			<div class="field checkbox">
				<input id="refreshnow" type="checkbox" name="refresh" value="now" <?php if ($refresh) echo " checked"; ?>>
				<label for="refreshnow"><?php echo $ARnls["refreshnow"]; ?></label>
			</div>
		</fieldset>
<?php
	}
?>