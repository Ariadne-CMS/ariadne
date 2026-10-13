<?php
	if (($this->CheckLogin('layout') || $this->CheckLogin('layout:read')) && $this->CheckConfig()) {
		$svn_enabled = (bool) $AR->SVN->enabled;
		$svn_info = array();
		$svn_status = array();
		$svn = null;

		if ($svn_enabled) {
			$filestore = $this->store->get_filestore_svn('templates');
			$svn = $filestore->connect($this->id);
			$svn_info = (array) $filestore->svn_info($svn);
			if ($svn_info['revision'] ?? null) {
				$svn_status = (array) $filestore->svn_status($svn);
			}
		} else {
			$filestore = $this->store->get_filestore('templates');
		}

		$arResult = array(
			'svn-enabled' => $svn_enabled,
			'svn-info' => $svn_info,
			'svn-status' => $svn_status,
			'templates' => array()
		);
		$known_filenames = array();
		$pinp = $this->data->config->pinp ?? array();

		foreach ($pinp as $type => $functions) {
			foreach ($functions as $function => $languages) {
				foreach ($languages as $language => $template_id) {
					$filename = $type.'.'.$function.'.'.$language.'.pinp';
					$known_filenames[$filename] = true;
					$arResult['templates'][] = array(
						'type' => $type,
						'function' => $function,
						'language' => $language,
						'local' => !isset($this->data->config->templates[$type][$function]),
						'private' => !empty($this->data->config->privatetemplates[$type][$function]),
						'mtime' => $filestore->mtime($this->id, $filename),
						'size' => $filestore->size($this->id, $filename),
						'svn' => !empty($svn_info['revision']),
						'svn-status' => $svn_status[$filename]['wc-status']['item'] ?? null
					);
				}
			}
		}

		// Keep versioned templates visible after a local delete/missing file, so
		// callers can still offer diff/revert/resolve actions for them.
		foreach ($svn_status as $filename => $status) {
			$item_status = $status['wc-status']['item'] ?? null;
			if (isset($known_filenames[$filename]) || ($item_status != 'missing' && $item_status != 'deleted')) {
				continue;
			}

			$file_meta = array();
			if ($item_status == 'missing') {
				$file_meta = array(
					'type' => $filestore->svn_propget($svn, 'ar:type', $filename),
					'function' => $filestore->svn_propget($svn, 'ar:function', $filename),
					'language' => $filestore->svn_propget($svn, 'ar:language', $filename),
					'private' => (bool) $filestore->svn_propget($svn, 'ar:private', $filename)
				);
			} else {
				foreach (($this->data->config->deleted_templates ?? array()) as $type => $functions) {
					foreach ($functions as $function => $languages) {
						foreach ($languages as $language => $default) {
							if ($filename == $type.'.'.$function.'.'.$language.'.pinp') {
								$file_meta = array(
									'type' => $type,
									'function' => $function,
									'language' => $language,
									'private' => !empty($this->data->config->deleted_privatetemplates[$type][$function])
								);
							}
						}
					}
				}
			}

			if (empty($file_meta['type']) || empty($file_meta['function']) || empty($file_meta['language'])) {
				continue;
			}
			$arResult['templates'][] = array(
				'type' => $file_meta['type'],
				'function' => $file_meta['function'],
				'language' => $file_meta['language'],
				'local' => false,
				'private' => $file_meta['private'],
				'mtime' => false,
				'size' => false,
				'svn' => true,
				'svn-status' => $item_status
			);
		}
	}
?>
