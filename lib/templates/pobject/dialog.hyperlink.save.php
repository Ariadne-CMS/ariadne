<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		require_once($this->store->get_config("code")."modules/mod_json.php");
		$arEditorSettings = $this->call('editor.ini');

		$attributes = array();
		$attributes['ar:type'] = $this->getdata('type');

		if ($this->getdata('type') == 'internal') {
			$attributes['href'] 		= $this->make_local_url($this->getdata('target'));
			$attributes['ar:path'] 		= $this->getdata('target');
			$attributes["ar:language"] 	= $this->getdata('language');
			$attributes["ar:behaviour"] 	= $this->getdata('behaviour');
			if ($this->getdata('anchor')) {
				$attributes["ar:anchor"] 	= $this->getdata('anchor');
				$attributes['href'] .= '#' . $this->getdata('anchor');
			}
		} else if ($this->getdata('type') == 'external') {
			$attributes['href'] 		= $this->getdata('url');
			$attributes["ar:behaviour"] 	= $this->getdata('behaviour');
			if ($this->getdata('anchor')) {
				$attributes["ar:anchor"] 	= $this->getdata('anchor');
				$attributes['href'] .= '#' . $this->getdata('anchor');
			}
		} else if ($this->getdata('type') == 'anchor') {
			$attributes['name'] 		= $this->getdata('name');
		}

		if (
			$arEditorSettings['link']['behaviours'][$this->getdata('behaviour')] &&
			$arEditorSettings['link']['behaviours'][$this->getdata('behaviour')]['attributes']
		) {
			$attributes = array_merge($attributes, $arEditorSettings['link']['behaviours'][$this->getdata('behaviour')]['attributes']);
		}

		$attributes_json = JSON::encode($attributes);
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