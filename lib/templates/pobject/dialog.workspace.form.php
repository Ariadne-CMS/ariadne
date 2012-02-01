<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		// Get the differences in the workspace for the children.
//		echo "<pre>";
//		print_r($this->store->getLayerstatus($this->path, true));
//		echo "</pre>";
				
	?>
<table>
	<thead>
		<th><?php echo $ARnls['name']; ?></th>
		<th><?php echo $ARnls['path']; ?></th>
		<th><?php echo $ARnls['ariadne:workspace:status']; ?></th>
		<th></th>
	</thead>
	<?php	
		foreach ($this->store->getLayerstatus($this->path, true) as $entry) {	
			$defaultnls = $entry['new']['data']->nls->default;
			$name = $entry['new']['data']->$defaultnls->name;
			$path = $entry['new']['path'];
			$changes = '';
			foreach ($entry['operation'] as $change) {
				$changes .= $ARnls["ariadne:workspace:" . $change] . "<br>";
			}
	?>
		<tr>
			<td><?php echo $name; ?></td>
			<td><?php echo $path; ?></td>
			<td><?php echo $changes; ?></td>	
			<td><input type="checkbox" name="workspace[<?php echo $entry['new']['path'];?>"></td>	
		</tr>
	<?php	}	?>
</table>
<?php		
	}
?>
