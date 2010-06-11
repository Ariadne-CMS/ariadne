<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("config") && $this->CheckConfig()) {
		$target = $this->getvar('target');
		$cancel = $this->getvar('cancel');

		$target_ob = current($this->get($this->make_path($target . '../'), "system.get.phtml"));
		$target_typetree = $target_ob->call("typetree.ini");
		if (
			$target_typetree[$target_ob->type][$this->type] || // The object is allowed under the target by the typetree.
			($this->CheckSilent('layout') && $this->getvar('override_typetree')) // Layout grant allows typetree changes, so allow typetree overrides as well.
		) {
			// This type is allowed.
			if (!$cancel) {
				if ($error=$this->call("system.linkto.phtml", array("target" => $target))) {
					$this->error=$error;
				}
				if (!$this->error) {
					?>
					<script type="text/javascript">
						if (window.opener && window.opener.muze.ariadne.explore.objectadded) {
							var currentpath = window.opener.muze.ariadne.registry.get('path');
							if (currentpath == '<?php echo $this->make_path($target . "../"); ?>') {
								window.opener.muze.ariadne.explore.objectadded();
							} else {
								window.opener.muze.ariadne.explore.tree.view('<?php echo $this->make_path($target . "../"); ?>');
							}							
						}
						window.close();
					</script>
					<?php
				} else {
					$this->call("show.error.phtml");
				}
			}
		} else {
			echo $ARnls['err:typetree_does_not_allow'];
		}
	}
?>