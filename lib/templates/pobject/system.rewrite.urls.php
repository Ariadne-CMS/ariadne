<?php
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$rewrite_urls = $this->getvar('rewrite_urls');

		if (is_array($rewrite_urls)) {
			$nls_list = $AR->nls->list;
			$nls_list['none'] = 'None';
			foreach ($rewrite_urls as $oldURL => $newURL) {
				if (substr($oldURL, -1) != '/') {
					$oldURL .= '/';
				}
				if (substr($newURL, -1) != '/') {
					$newURL .= '/';
				}
				foreach ($nls_list as $nls => $nls_name) {
					if ($this->data->$nls) {
						$nls_fields = array( 'page' , 'summary' );
						foreach ($nls_fields as $nls_field) {
							$page = $this->data->$nls->$nls_field;
							if ($page) {
								$regexp = '|' . str_replace('|', '\|', $oldURL) . '|';
								$page = preg_replace($regexp, $newURL, $page);
								if ($page != $this->data->$nls->$nls_field) {
									$this->data->$nls->$nls_field = $page;
								}
							}
						}
					}
					if (is_array($this->data->custom[ $nls ])) {
						foreach ($this->data->custom[ $nls ] as $customField => $page) {
							if ($page) {
								$regexp = '|' . str_replace('|', '\|', $oldURL) . '|';
								$page = preg_replace($regexp, $newURL, $page);
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