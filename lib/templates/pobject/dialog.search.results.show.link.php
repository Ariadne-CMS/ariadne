<?php
	$ARCurrent->nolangcheck = true;
	if( $this->CheckConfig() ) {
		require_once($this->store->get_config("code")."modules/mod_unicode.php");

		if (!($arLanguage ?? null)) {
			$arLanguage=$nls;
		}
		if (isset($data->$arLanguage)) {
			$nlsdata=$data->$arLanguage;
		}
		$icon=$this->call('system.get.icon.php', array('size' => 'small'));
?>
<div class='object'><a title="<?php echo htmlspecialchars($nlsdata->name); ?>"
	href="javascript:top.View('<?php echo $this->path; ?>','<?php echo str_replace('"','&quot;',AddCSlashes($nlsdata->name,ARESCAPE)); ?>');"><img
	class="icon" src="<?php echo $icon; ?>"
	alt="<?php echo $this->path; ?>"> <?php
		if (mb_strlen($nlsdata->name, "utf-8") > 32) {
			$name = htmlspecialchars(mb_substr($nlsdata->name, 0, 32, "utf-8") . "...");
		} else {
			$name = htmlspecialcharS($nlsdata->name);
		}
		if (!$this->CheckSilent("read")) {
			echo "<font color=\"#CCCCCC\">$name</font>";
		} else {
			echo "$name";
		}
	?></a></div>
<?php
	}
?>
