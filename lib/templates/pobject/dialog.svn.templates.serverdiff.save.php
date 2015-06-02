<?php
	ldDisablePostProcessing();
	$ARCurrent->nolangcheck=true;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		set_time_limit(0);
		$arCallArgs["colorize"] = true;
		$arCallArgs['revision'] = $this->getdata('revision') ? $this->getdata('revision') : "HEAD";
		$result = $this->call("system.svn.diff.php", $arCallArgs);
		if( $result != "" ) {
			echo $result;
		} else {
			echo "<pre class='svn_result'>".$ARnls["ariadne:svn:nomod"]."</pre>";
		}
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
