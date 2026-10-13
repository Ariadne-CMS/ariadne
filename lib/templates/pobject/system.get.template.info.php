<?php
	if (($this->CheckLogin('layout') || $this->CheckLogin('layout:read')) && $this->CheckConfig()) {
		$filename = $type.'.'.$function.'.'.$language.'.pinp';
		$template_list = $this->call('system.get.templates.info.php');
		$arResult = ar::error('Template not found', 404);

		foreach ($template_list['templates'] as $template_info) {
			if ($template_info['type'] != $type
				|| $template_info['function'] != $function
				|| $template_info['language'] != $language) {
				continue;
			}

			if ($template_info['mtime'] === false
				&& $template_info['svn-status'] != 'missing'
				&& $template_info['svn-status'] != 'deleted') {
				$arResult = ar::error('Template not accessible', 501);
				break;
			}

			$arResult = array(
				'svn' => $template_info['svn'],
				'ctime' => $template_info['mtime'],
				'mtime' => $template_info['mtime'],
				'size' => $template_info['size']
			);
			if ($template_info['svn']) {
				$arResult += array(
					'svn-info' => $template_list['svn-info'],
					'svn-status' => $template_list['svn-status'][$filename] ?? null
				);
			}
			break;
		}
	}
?>
