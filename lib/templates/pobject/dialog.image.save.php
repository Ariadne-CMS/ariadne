<?php
	$ARCurrent->nolangcheck=true;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$arEditorSettings = $this->call('editor.ini');

		$attributes = array();

		$attributes['ar:path'] 		= $this->getdata('target');
		$attributes['ar:style']		= $this->getdata('style');
		if ($this->getdata('align') != "none") {
			$attributes['align']		= $this->getdata('align');
		}
		if ($this->getdata('alttext')) {
			$attributes['alt']		= $this->getdata('alttext');
			$attributes['title']		= $this->getdata('alttext');
		}
		$attributes['src']		= $this->make_local_url($this->getdata('target')) . $arEditorSettings['image']['styles'][$this->getdata('style')]['template'];

		if (
			$arEditorSettings['image']['styles'][$this->getdata('style')] &&
			$arEditorSettings['image']['styles'][$this->getdata('style')]['attributes']
		) {
			$attributes = array_merge($attributes, $arEditorSettings['image']['styles'][$this->getdata('style')]['attributes']);
		}

		$attributes_json = json_encode($attributes);
		?>
		<script type="text/javascript">
			if (window.opener && window.opener.callback) {
				window.opener.callback(<?php echo $attributes_json; ?>);
			}
			window.close();
		</script>
		<?php
	}
?>
