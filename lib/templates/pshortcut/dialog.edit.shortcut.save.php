<?php
	$ARCurrent->nolangcheck=true;
	$arIsNewObject = $this->arIsNewObject;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);      
		exit;
	}

	$vtype=$this->call("system.save.shortcut.phtml",$arCallArgs);
	if ($this->error) {
		?>
			<font color="red"><?php echo $this->error; ?></font><p>
		<?php
	} else {
		?>
			<script>
				if (top.window.opener) {
					if (top.window.opener.objectadded) {
						top.window.opener.objectadded('<?php echo $this->type; ?>','<?php echo AddCSlashes($nlsdata->name, ARESCAPE); ?>','<?php echo $this->path; ?>'); 
					}
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
