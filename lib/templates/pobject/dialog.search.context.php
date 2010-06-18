<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
		$arPath = $this->getdata("arPath", "none");
		if( !$arPath ) {
			$arPath = $this->path;
		}
?>
<fieldset id="context">
	<legend><?php echo $ARnls["contextsearch"]; ?></legend>
	<div class="field">
		<label for="arPath" class="required"><?php echo $ARnls["path"]; ?></label>
		<input id="arPath" type="text" name="arPath" value="<?php echo $arPath; ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="searchname"><?php echo $ARnls["name"]; ?></label>
		<input type="text" name="searchname" id="searchname" value="<?php $this->showdata("searchname","none"); ?>" class="inputline">
	</div>
	<div class="field">
		<label for="searchtext"><?php echo $ARnls["text"]; ?></label>
		<input type="text" name="searchtext" id="searchtext" value="<?php $this->showdata("searchtext","none"); ?>" class="inputline">
	</div>
	<div class="field">
		<label for="arimplements"><?php echo $ARnls["type"]; ?></label>
		<select name="arimplements" id="arimplements">
			<?php
				echo "<option value=''>".$ARnls["all"]."</option>\n";
				$this->call('dialog.types.optionlist.php', array("selected" => $this->getdata("arimplements", "none")));
			?>
		</select>
	</div>
</fieldset>
<?php
		$this->call("dialog.search.results.php", array("context" => 1) );
	}
?>
