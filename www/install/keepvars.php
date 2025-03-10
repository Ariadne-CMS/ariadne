<div class="keepvars">
<?php
	include_once("getvars.php");
	foreach ($postvars as $postkey => $postvalue) {
		if (is_array($postvalue)) {
			foreach ($postvalue as $subkey => $subval) {
			?>
				<input type="hidden" name="<?php echo htmlspecialchars($postkey??''); ?>[<?php echo $subkey; ?>]" value="<?php echo htmlspecialchars($subval??''); ?>">
			<?php
			}
		} else {
	?>
		<input type="hidden" name="<?php echo htmlspecialchars($postkey??''); ?>" value="<?php echo htmlspecialchars($postvalue??''); ?>">
	<?php
		}
	}
?>
</div>
