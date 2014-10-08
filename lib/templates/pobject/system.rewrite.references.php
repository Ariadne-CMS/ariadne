<?php
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$rewrite_references = $this->getvar("rewrite_references");
		if (is_array($rewrite_references)) {
			foreach ($rewrite_references as $oldPath => $newPath) {
				if ($this->data->path) {
					if (strpos($this->data->path, $oldPath) === 0) {
						$this->data->path = $newPath . substr( $this->data->path, strlen( $oldPath ) );
					}
				}

				if (is_array($this->properties['references'])) {
					foreach ($this->properties['references'] as $i => $record) {
						if ($record['path']) {
							if (strpos($record['path'], $oldPath) === 0) {
								$this->properties['references'][$i]['path'] = $newPath . substr( $record['path'], strlen( $oldPath ) );
							}
						}
					}
				}

				$nls_list = $AR->nls->list;
				$nls_list['none'] = 'none';
				foreach ($nls_list as $nls => $nls_name) {
						if ($this->data->$nls) {
							$nls_fields = array( 'page' , 'summary' );
							foreach ($nls_fields as $nls_field) {
								$page = $this->data->$nls->$nls_field;
								if ($page && is_string($page)) {
									$regexp = '|' . str_replace('|', '\|', $oldPath) . '|';
									$page = preg_replace($regexp, $newPath, $page);
									if ($page != $this->data->$nls->$nls_field) {
										$this->data->$nls->$nls_field = $page;
									}
								}
							}
						} else if (is_array($this->data->custom[ $nls ])) {
							foreach ($this->data->custom[ $nls ] as $customField => $page) {
								if ($page && is_string($page)) {
									$regexp = '|' . str_replace('|', '\|', $oldPath) . '|';
									$page = preg_replace($regexp, $newPath, $page);
									if ($page != $this->data->custom[$nls][$customField]) {
										$this->data->custom[$nls][$customField] = $page;
									}
								}
							}
						}
				}
			}

			$this->save();
		}
	}
?>