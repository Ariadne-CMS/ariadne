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

		
?>
<fieldset id="data">
	<legend><?php echo $ARnls["delete"]; ?> <span class="crumbs" title="<?php echo $oldcrumbs; ?>"><?php echo $crumbs; ?></span></legend>
	<?php if ($total == 0) {
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