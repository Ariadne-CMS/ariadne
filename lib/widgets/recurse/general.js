<script>
	function wgRecurse(path, action, target) {
		<?php if ($wgRecurseConfirm) { ?>
			if (confirm('<?php echo $wgRecurseConfirm; ?>')) {
		<?php } ?>
		recursewindow=window.open('<?php echo $this->store->root; ?>'+path+'recurse.phtml?action='+escape(action)+'&target='+escape(target),
		'recursewindow','directories=no,location=no,menubar=no,status=no,toolbar=no,resizable=yes,width=450,height=100');
		recursewindow.focus();
		<?php if ($wgRecurseConfirm) { ?>
			}
		<?php } ?>
	}
</script>