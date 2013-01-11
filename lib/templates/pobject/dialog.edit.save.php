<?php
	$ARCurrent->nolangcheck=true;
	$arIsNewObject=$this->arIsNewObject;
	$this->call("system.save.data.phtml",$arCallArgs);
	if ($this->error) {
		?>
			<font color="red" face="arial,helvetica,sans-serif" size=+1><?php echo $this->error; ?></font><p>
		<?php
	} else {
		$this->unlock();
		?>
			<script type="text/javascript"> 
				if (top.window.opener) {
					if (top.window.opener.muze.dialog && window.opener.muze.dialog.hasCallback( window.name, 'submit') ) {
						window.opener.muze.dialog.callback( window.name, 'submit', { 
							'type' : '<?php echo $this->type; ?>', 
							'name' : '<?php echo AddCSlashes($this->nlsdata->name, ARESCAPE); ?>',
							'path' : '<?php echo $this->path; ?>',
							'url'  : '<?php echo $this->make_url($this->path); ?>'
						} );
					}

					if (top.window.opener.objectadded && top.window.opener.muze && top.window.opener.muze.ariadne ) {
						currentpath = window.opener.muze.ariadne.registry.get('path');
						if (currentpath == '<?php echo $this->parent; ?>') {
							window.opener.muze.ariadne.explore.objectadded();
						} else if (currentpath == '<?php echo $this->path; ?>') {
							window.opener.muze.ariadne.explore.tree.refresh('<?php echo $this->path; ?>');
							window.opener.muze.ariadne.explore.sidebar.view('<?php echo $this->path; ?>');
							window.opener.muze.ariadne.explore.browseheader.view('<?php echo $this->path; ?>');
						} else {
							// Fallback for shortcuts.
							window.opener.muze.ariadne.explore.objectadded();
						}
					}
					<?php	if ($this->path == $AR->user->path) {  ?>
							window.opener.muze.ariadne.explore.viewpane.view('<?php echo $this->path; ?>');
							window.opener.muze.ariadne.explore.toolbar.load('<?php echo $this->path; ?>');
					<?php	}	?>

					<?php
						if (!$arIsNewObject || $this->getdata("arCloseWindow", "none")) {
					?>
							top.window.close();
					<?php
						} else {
					?>
							top.window.location.href='dialog.add.php';
					<?php
						}
					?>
				} else if (top.window.dialogArguments) {
					arr=new Array();
					arr['type']='<?php echo $this->type; ?>';
					arr['name']='<?php echo AddCSlashes($this->nlsdata->name, ARESCAPE); ?>';
					arr['path']='<?php echo $this->path; ?>';
					top.window.returnValue=arr;
					top.window.close();
				}
			</script>
		<?php
	}
?>