<?php
	$ARCurrent->nolangcheck=true;
//	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		if ($this->getvar('sources')) {
			$sources = $this->getvar("sources");
		} else {
			 $sources = array($this->path);
		}

		$target = $this->getvar("target");
		if (!$target) {
			// FIXME: Does this make sense?
			$target = $this->path;
		}

		$pathmode = $this->getvar("pathmode");
		if ( !isset( $jail ) ) {
			$jail = "";
		}
?>
<script type="text/javascript">
	var updateTimer = false;

	function callback(path) {
		var jail = "<?php echo $jail; ?>";
		if (path.indexOf(jail) == 0) {
			path = path.substring(jail.length-1, path.length);
		}
		<?php if ($pathmode == "siterelative") { ?>
			if (path.indexOf(document.getElementById("parent").value == 0)) {
				document.getElementById("filename").value = path.substring(document.getElementById("parent").value.length, path.length);
			}
		<?php } else { ?>
		if (document.getElementById("parent")) {
		    document.getElementById("parent").value = path;
		} else {
		    document.getElementById("relativetarget").value = path;
		}
		<?php } ?>
		updateTarget();
	}

	function checkGrants() {
		var url = "check.grant.ajax?grant=add&target=" + escape(document.getElementById("target").value);
		var result = muze.load(url, true, false);

		if (result != "" && result != 1) {
			document.getElementById("relativetarget").style.backgroundColor = "#ffdddd";
			document.getElementById("targetError").style.display = "block";
		} else {
			document.getElementById("relativetarget").style.backgroundColor = "white";
			document.getElementById("targetError").style.display = "none";
		}
	}

	function updateTarget() {
		if (!(document.getElementById("relativetarget"))){
		    relativetarget = document.getElementById("parent").value.concat(document.getElementById("filename").value);
		} else {
		    relativetarget = document.getElementById("relativetarget").value;
		}
		var jail = "<?php echo $jail; ?>";
		document.getElementById("target").value = (jail + relativetarget).replace("//", "/");

		if (updateTimer) {
			window.clearTimeout(updateTimer);
		}
		updateTimer = window.setTimeout(checkGrants, 300);
	}

</script>
<fieldset id="data" class="browse">
<?php
	$origin = $this->getvar("origin");
	if ($origin == "copy") {
	    echo '<legend>' . $ARnls["ariadne:copy"] . '</legend>';
	} elseif ($origin == "move"){
	    echo '<legend>'. $ARnls["ariadne:move"] . '</legend>';
	} else {
	    echo '<legend>' . $ARnls["ariadne:rename"] . '</legend>';
	}

		foreach ($sources as $source) {
			$sourceob = current($this->get($source, "system.get.phtml"));

			$jail = ar::acquire('settings.jail');

			if (!$jail) {
				$jail = '/';
			}
			if (substr($jail, -1) != "/") {
				$jail .= "/";
			}

			if ($jail) {
				$target = preg_replace("|^$jail|", '/', $target);
			}

			$crumbs = '';
			$path = '';

			$parents = $sourceob->parents($sourceob->path, 'system.get.name.phtml', '', $jail);
			$parentpaths = $sourceob->parents($sourceob->path, 'system.get.path.phtml', '', $jail);
			$path = array_pop($parentpaths);

			if ( strpos( $path, $jail ) === 0 ) {
				$path = substr( $path, strlen($jail)-1 );
			}

			array_pop($parents); //remove current item from the list.
			array_shift( $parents ); // remove site jail from the list.
			foreach ($parents as $name) {
				$crumbs .= "/ $name";
			}

			$oldcrumbs = $crumbs;
			if (strlen($crumbs) > 18) {
				$crumbs = mb_substr($crumbs, 0, 12, "utf-8") . "..." . mb_substr($crumbs, strlen($crumbs)-5, strlen($crumbs), "utf-8");
				if (strlen($crumbs) >= strlen($oldcrumbs)) {
					$crumbs = $oldcrumbs;
				}
			}

			$crumbs = htmlspecialchars( $crumbs??'' . "/ " . $sourceob->nlsdata->name??'' );
			$oldcrumbs = htmlspecialchars( $oldcrumbs??'' . "/ " . $sourceob->nlsdata->name??'' );

			if( !isset($ARCurrent->arTypeTree) ) {
				$sourceob->call('typetree.ini');
			}
			$icons = $ARCurrent->arTypeIcons;
			$names = $ARCurrent->arTypeNames;

			$icon = $ARCurrent->arTypeIcons[$sourceob->type]['medium'] ? $ARCurrent->arTypeIcons[$sourceob->type]['medium'] : $sourceob->call("system.get.icon.php", array('size' => 'medium'));

			$iconalt = $sourceob->type;
			if ( $sourceob->implements("pshortcut") ) {
				$overlay_icon = $icon;
				$overlay_alt = $sourceob->type;
				if ( $ARCurrent->arTypeIcons[$sourceob->vtype]['medium'] ) {
					$icon = $ARCurrent->arTypeIcons[$sourceob->vtype]['medium'];
				} else {
					$icon = current($sourceob->get($sourceob->data->path, "system.get.icon.php", array('size' => 'medium')));
				}
				$iconalt = $sourceob->vtype;
			}

			if ($origin == "copy" && !$sourceob->CheckSilent("read")){
			       $checkfailed = true;
			} else if ($origin == "move" && !$sourceob->CheckSilent("edit")) { //dialog.move.php zegt delete && config, explore.sidebar zegt edit.
			       $checkfailed = true;
			} else {
				$checkfailed = false;
			}

			if($checkfailed){
			    echo '<div class="browse checkfailed">';
			    echo '<span class="error">' . $ARnls["err:nogrants"] .'</span><br>';
			} else {
			    echo '<div class="browse">';
			}
			echo '<img src="' . $icon . '" alt="' . htmlspecialchars($iconalt??'') . '" title="' . htmlspecialchars($iconalt??'') . '" class="typeicon">';
			if ( $overlay_icon ?? null ) {
			echo '<img src="' . $overlay_icon . '" alt="' . htmlspecialchars($overlay_alt??'') . '" title="' . htmlspecialchars($overlay_alt??'') . '" class="overlay_typeicon">';
			}
			echo '<div class="name">' . $sourceob->nlsdata->name . ' ';
			echo '( <span class="crumbs" title="' . $oldcrumbs . '">' . $crumbs . '</span> )';
			echo '</div>';
			echo '<div class="path">' . $path . '</div><br>';
			echo '</div>';
		}
?>

	<div class="browse_wrapper">
		<div class="field">
		<label for="target" class="required"><?php echo $ARnls["target"]; ?></label>

		<?php if ($pathmode == "filename") { ?>
				<div class="inputline"><input id="parent" type="text" name="parent" value="<?php echo end($parentpaths); ?>" disabled><input id="filename" type="text" name="filename" value="<?php echo basename($target); ?>" class="wgWizAutoFocus" onchange="updateTarget();" onkeyup="updateTarget();"></div>
				<input type="hidden" id="target" name="target" value="<?php echo htmlentities(str_replace("//", "/", $jail . $target)) ?>">
				<!--No browse button, not needed for rename-->
		<?php } else if ($pathmode == "parent") { ?>
				<div class="inputline"><input id="parent" type="text" name="parent" value="<?php echo end($parentpaths); ?>" class="wgWizAutoFocus" onchange="updateTarget();" onkeyup="updateTarget();"><?php if (count($sources)==1){ ?> <input disabled id="filename" type="text" name="filename" value="<?php echo basename($target); ?>"><?php } ?> </div>
				<input type="hidden" id="target" name="target" value="<?php echo htmlentities(str_replace("//", "/", $jail . $target)) ?>">
				<input class="button" type="button" value="<?php echo $ARnls['browse']; ?>" title="<?php echo $ARnls['browse']; ?>" onclick='window.open("<?php echo $this->make_ariadne_url($jail); ?>" + document.getElementById("parent").value + "dialog.browse.php", "browse", "height=480,width=750"); return false;'>
		<?php } else if ($pathmode == "siterelative") { ?>
				<input id="parent" type="hidden" name="parent" value="<?php echo $this->currentsite(); ?>" class="wgWizAutoFocus"><?php if (count($sources)==1){ ?> <input id="filename" class="inputline" type="text" name="filename" onchange="updateTarget();" onkeyup="updateTarget();" value="<?php echo preg_replace("|^" . $this->currentsite() . "|", "", $target); ?>"><?php } ?>
				<input type="hidden" id="target" name="target" value="<?php echo htmlentities(str_replace("//", "/", $jail . $target)) ?>">
				<input class="button" type="button" value="<?php echo $ARnls['browse']; ?>" title="<?php echo $ARnls['browse']; ?>" onclick='window.open("<?php echo $this->make_ariadne_url($jail); ?>" + document.getElementById("target").value + "dialog.browse.php?pathmode=siterelative", "browse", "height=480,width=750"); return false;'>
		<?php } else { ?>
				<input id="relativetarget" type="text" name="relativetarget" value="<?php echo $target; ?>" class="inputline wgWizAutoFocus" onchange="updateTarget();" onkeyup="updateTarget();">
				<input type="hidden" id="target" name="target" value="<?php echo htmlentities(str_replace("//", "/", $jail . $target)) ?>">
				<input class="button" type="button" value="<?php echo $ARnls['browse']; ?>" title="<?php echo $ARnls['browse']; ?>" onclick='window.open("<?php echo $this->make_ariadne_url($jail); ?>" + document.getElementById("relativetarget").value + "dialog.browse.php", "browse", "height=480,width=750"); return false;'>
				<div id="targetError" style="display: none;"><?php echo $ARnls['err:no_add_on_target']; ?></div>
		<?php } ?>
		<div class="clear"></div>
		</div>
	</div>
	<script type="text/javascript">
		checkGrants();
	</script>
<?php
	if ($this->CheckSilent("layout")) { ?>
		<div class="field checkbox">
		<input id="override_typetree" type="checkbox" name="override_typetree" value="1">
		<label for="override_typetree"><?php echo $ARnls['ariadne:override_typetree']; ?></label>
		</div>
<?php	} ?>
</fieldset>
<?php	//}
?>
