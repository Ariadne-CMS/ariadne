<?php
	$ARCurrent->nolangcheck=true;

	if ($this->CheckLogin("delete") && $this->CheckConfig()) {
		$root = $this->getvar("root") ? $this->getvar("root") : $this->currentsite();
		if (substr($root, -1) != "/") {
			$root .= "/";
		}

		$query = "object.path =~ '" . $target . "%' order by path DESC";
		$total = $this->count_find($this->path, $query);
		$total--; // children of current path.
		$crumbs = '';
		$path = '';
		$parents = $this->parents($this->path, 'system.get.name.phtml', '', $root);
		$parentpaths = $this->parents($this->path, 'system.get.path.phtml', '', $root);
		$path = array_pop($parentpaths);

		if (substr($path, 0, strlen($root)) == $root) {
			$path = substr($path, strlen($root), strlen($path));
		}

		$path = "/" . $path;

		array_pop($parents); //remove current item from the list.
		foreach ($parents as $name) {
			$crumbs .= $name . " / ";
		}

		$oldcrumbs = $crumbs;
		if (strlen($crumbs) > 18) {
			$crumbs = mb_substr($crumbs, 0, 12, "utf-8") . "..." . mb_substr($crumbs, strlen($crumbs)-5, strlen($crumbs), "utf-8");
			if (strlen($crumbs) >= strlen($oldcrumbs)) {
				$crumbs = $oldcrumbs;
			}
		}
		$crumbs = htmlspecialchars($crumbs . $nlsdata->name);
		$oldcrumbs = htmlspecialchars($oldcrumbs);

		if( !$ARCurrent->arTypeTree ) {
			$this->call('typetree.ini');
		}
		$icons = $ARCurrent->arTypeIcons;
		$names = $ARCurrent->arTypeNames;

		$icon = $ARCurrent->arTypeIcons[$this->type]['medium'] ? $ARCurrent->arTypeIcons[$this->type]['medium'] : $this->call("system.get.icon.php", array('size' => 'medium'));

		$iconalt = $this->type;
		if( 1 || $this->implements("pshortcut") ) {
			$overlay_icon = $icon;
			$overlay_alt = $this->type;
			$icon = $ARCurrent->arTypeIcons[$this->vtype]['medium'] ? $ARCurrent->arTypeIcons[$this->vtype]['medium'] : current($this->get($this->data->path, "system.get.icon.php", array('size' => 'medium')));
			$iconalt = $this->vtype;
		}
?>
<fieldset id="data" class="delete">
	<legend>
<?php echo $ARnls["delete"]; ?> <?php echo $names[$this->type] . ": "; echo $nlsdata->name; ?> (<span class="crumbs" title="<?php echo $oldcrumbs; ?>"><?php echo $crumbs; ?></span>)</legend>
<img src="<?php echo $icon; ?>" alt="<?php echo htmlspecialchars($iconalt); ?>" title="<?php echo htmlspecialchars($iconalt); ?>" class="typeicon">
	<?php
		if( $overlay_icon ) {
	?>
	<img src="<?php echo $overlay_icon; ?>" alt="<?php echo htmlspecialchars($overlay_alt); ?>" title="<?php echo htmlspecialchars($overlay_alt); ?>" class="overlay_typeicon">
	<?php
		}
	?>	<?php if ($total == 0) {
		echo $ARnls["q:removeobject"];
	} else { ?>
		<span class="crumbs" title="<?php echo $oldcrumbs; ?>">
			<?php echo $crumbs; ?>
		</span> (<?php echo $path; ?>)
		<?php echo sprintf($ARnls['q:removeall'], '', $total); ?>

		<div class="field checkbox">
			<input type="checkbox" id="childrenonly" name="childrenonly" value="true">
			<label for="childrenonly"><?php echo $ARnls['ariadne:childrenonly']; ?></label> 
		</div>

	<?php } ?>
</fieldset>
<?php } ?>