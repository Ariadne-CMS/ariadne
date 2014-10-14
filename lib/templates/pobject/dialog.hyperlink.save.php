<?php
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);      
		exit;
	}
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {

		$result = array( 'attributes' => array() );

		$arEditorSettings = $this->call('editor.ini');
		// FIXME: attributes moeten naar json result ['attributes'] ipv direct in root scope gedrukt te worden.
		$attributes = array();
		$attributes['ar:type'] = $this->getvar('artype');

		$arpath = $this->getvar('arpath');
		$arlanguage = $this->getvar('arlanguage');
		$aranchor = str_replace('#', '', $this->getvar('aranchor'));
		$arbehaviour = $this->getvar('arbehaviour');
		$arname = $this->getvar('name');
		$artype = $this->getvar('artype');
		$arurl  = $this->getvar('url');
		$arnofollow = $this->getvar('arnofollow');

		if ($artype == 'internal') {
			$result['href']                        = $this->make_url($arpath, $arlanguage);
			$result['attributes']['ar:path']       = $arpath;
			$result['attributes']["ar:language"]   = $arlanguage;
			$result['attributes']["ar:behaviour"]  = $arbehaviour;
			if ($aranchor) {
				$result['attributes']['ar:anchor']  = $aranchor;
				$result['href']       .= '#' .$aranchor;
			}
		} else if ($artype == 'external') {
			$result['href'] 		= $arurl;
			$result['attributes']["ar:behaviour"] 	= $arbehaviour;
			if ($aranchor) {
				$result['attributes']['ar:anchor']  = $aranchor;
				$result['href'] .= '#' . $aranchor;
			}
		} else if ($artype == 'anchor') {
			$result['name'] 		= $arname;
		}

		if ($arnofollow) {
			$result['attributes']['rel'] = "nofollow";
		}

		$result['attributes']['ar:type'] = $artype;

		if (
			$arEditorSettings['link']['behaviours'][$arbehaviour] &&
			$arEditorSettings['link']['behaviours'][$arbehaviour]['attributes']
		) {
			$result['attributes'] = array_merge($result['attributes'], $arEditorSettings['link']['behaviours'][$arbehaviour]['attributes']);
		}

		?>
		<script type="text/javascript">
			if (window.opener) {
				if (window.opener.muze && window.opener.muze.dialog && window.opener.muze.dialog.hasCallback( window.name, 'submit') ) {
					window.opener.muze.dialog.callback( window.name, 'submit', <?php echo json_encode($result); ?> );
				} else if (window.opener.callback) {
					window.opener.callback(<?php echo json_encode($result); ?>);
				}
			}
			window.close();
		</script>
		<?php
	}
?>