<?php
	$ARCurrent->nolangcheck=true;

	if ($this->CheckLogin("delete") && $this->CheckConfig()) {
		$query = "object.path =~ '" . $target . "%' order by path DESC";
		$total = $this->count_find($this->path, $query);
		$total--; // children of current path.
		$crumbs = '';
		$parents = $this->parents($this->path, 'system.get.name.phtml', '', $this->currentsite());
		array_pop($parents); //remove current item from the list.
		foreach ($parents as $name) {
			$crumbs .= $name . " / ";
		}

		$crumbs .= $nlsdata->name;
		if (strlen($crumbs) > 25) {
			$oldcrumbs = htmlspecialchars($crumbs);
			$crumbs = htmlspecialchars(mb_substr($crumbs, 0, 12, "utf-8") . "..." . mb_substr($crumbs, strlen($crumbs)-12, strlen($crumbs), "utf-8"));
		} else {
			$crumbs = htmlspecialchars($crumbs);
		}

?>
<fieldset id="data">
	<legend><?php echo $ARnls["delete"]; ?> <span class="crumbs" title="<?php echo $oldcrumbs; ?>"><?php echo $crumbs; ?></span></legend>
	<?php if ($total == 0) {
		echo $ARnls["q:removeobject"];
	} else { ?>
		<span class="crumbs" title="<?php echo $oldcrumbs; ?>">
			<?php echo $crumbs; ?>
		</span> (<?php echo $this->path; ?>)
		<?php echo sprintf($ARnls['q:removeall'], '', $total); ?>

		<div class="field checkbox">
			<input type="checkbox" id="childrenonly" name="childrenonly" value="true">
			<label for="childrenonly"><?php echo $ARnls['ariadne:childrenonly']; ?></label> 
		</div>

	<?php } ?>
</fieldset>
<?php } ?>