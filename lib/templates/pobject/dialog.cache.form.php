<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("edit") && $this->CheckConfig()) {
		$cacheSettings = $this->data->config->cacheSettings??null;
		if (isset($cacheSettings['serverCache'])) {
			$cacheconfig = $cacheSettings['serverCache'];
		} else {
			$cacheconfig=$this->data->config->cacheconfig??null;
		}

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
		<fieldset id="browserCache">
			<legend><?php echo $ARnls["ariadne:cache:browsercaching"]; ?></legend>
			<div class="field checkbox">
				<input type="hidden" name="browserCachePrivate" value="0">
				<input type="hidden" name="browserCacheNoCache" value="0">
				<input type="hidden" name="browserCacheNoStore" value="0">
				<input type="hidden" name="browserCacheMustRevalidate" value="0">
				<input type="hidden" name="browserCacheNoTransform" value="0">
				<input type="hidden" name="browserCacheProxyRevalidate" value="0">
				<input id="browserCachePrivate" type="checkbox" name="browserCachePrivate" value="1" <?php if ($cacheSettings["browserCachePrivate"]??null) { echo "checked"; } ?>>
				<label for="browserCachePrivate"><?php echo $ARnls["ariadne:cache:private"]; ?></label>
			</div>
			<div class="field text">
				<label for="browserCacheMaxAge"><?php echo $ARnls["ariadne:cache:max-age"]; ?></label>
				<input type="text" id="browserCacheMaxAge" name="browserCacheMaxAge" size="4" maxlength="6" value="<?php echo (($cacheSettings["browserCacheMaxAge"]??null) !== '') ? (int)($cacheSettings["browserCacheMaxAge"]??null) : ""; ?>">
			</div>
			<div class="field checkbox">
				<input id="browserCacheNoCache" type="checkbox" name="browserCacheNoCache" value="1" <?php if ($cacheSettings["browserCacheNoCache"]??null) { echo "checked"; } ?>>
				<label for="browserCacheNoCache"><?php echo $ARnls["ariadne:cache:no-cache"]; ?></label>
			</div>
			<div class="field checkbox">
				<input id="browserCacheMustRevalidate" type="checkbox" name="browserCacheMustRevalidate" value="1" <?php if ($cacheSettings["browserCacheMustRevalidate"]??null) { echo "checked"; } ?>>
				<label for="browserCacheMustRevalidate"><?php echo $ARnls["ariadne:cache:must-revalidate"]; ?></label>
			</div>
			<div class="field checkbox">
				<input id="browserCacheNoStore" type="checkbox" name="browserCacheNoStore" value="1" <?php if ($cacheSettings["browserCacheNoStore"]??null) { echo "checked"; } ?>>
				<label for="browserCacheNoStore"><?php echo $ARnls["ariadne:cache:no-store"]; ?></label>
			</div>
		</fieldset>
		<fieldset id="proxyCache">
			<div class="field text">
				<label for="browserCacheSMaxAge"><?php echo $ARnls["ariadne:cache:s-max-age"]; ?></label>
				<input type="text" id="browserCacheSMaxAge" name="browserCacheSMaxAge" size="4" maxlength="6" value="<?php echo (($cacheSettings["browserCacheSMaxAge"]??null)!== '') ? (int)($cacheSettings["browserCacheSMaxAge"]??null) : ""; ?>">
			</div>
			<legend><?php echo $ARnls["ariadne:cache:proxycaching"]; ?></legend>
			<div class="field checkbox">
				<input id="browserCacheNoTransform" type="checkbox" name="browserCacheNoTransform" value="1" <?php if ($cacheSettings["browserCacheNoTransform"]??null) { echo "checked"; } ?>>
				<label for="browserCacheNoTransform"><?php echo $ARnls["ariadne:cache:no-transform"]; ?></label>
			</div>
			<div class="field checkbox">
				<input id="browserCacheProxyRevalidate" type="checkbox" name="browserCacheProxyRevalidate" value="1" <?php if ($cacheSettings["browserCacheProxyRevalidate"]??null) { echo "checked"; } ?>>
				<label for="browserCacheProxyRevalidate"><?php echo $ARnls["ariadne:cache:proxy-revalidate"]; ?></label>
			</div>
		</fieldset>

<?php
	}
?>
