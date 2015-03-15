<?php
	if ( ( $this->CheckLogin('layout') || $this->CheckLogin('layout:read') ) && $this->CheckConfig() ) {
        $file = "";
        if ($this->data->config->pinp[$type][$function][$language]) {
            $template=$type.".".$function.".".$language.".pinp";
			$svn_enabled = $AR->SVN->enabled;

			if ($svn_enabled) {
				$filestore = $this->store->get_filestore_svn("templates");
				$svn = $filestore->connect($this->id);
				$svn_info = $filestore->svn_info($svn);
				if ( $svn_info['revision'] ) {
					$svn_status = $filestore->svn_status($svn);
				} else {
					$svn_enabled = false; // this library is not under revision control
				}
			} else {
				$filestore = $this->store->get_filestore("templates");
			}

            if ($filestore->exists($this->id, $template)) {
                $arResult = array(
					'svn'   => $svn_enabled,
					'ctime' => $filestore->mtime($this->id, $template),
					'mtime' => $filestore->mtime($this->id, $template),
					'size'  => $filestore->size($this->id, $template)
				);
				if ( $svn_enabled ) {
					$arResult += array(
						'svn-info' => $svn_info,
						'svn-status' => $svn_status[$template]
					);
				}
            } else {
				$arResult = ar::error('Template not accessible', 501);
			}
        } else {
			$arResult = ar::error('Template not found', 404);
		}
	}
?>