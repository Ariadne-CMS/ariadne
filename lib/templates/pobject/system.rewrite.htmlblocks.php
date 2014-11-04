<?php
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$rewrite_urls = $this->getvar('rewrite_urls');
		$dosave = false;

		if (is_array($rewrite_urls)) {
			$nls_list = $AR->nls->list;
			$nls_list['none'] = 'None';
			foreach ($rewrite_urls as $oldURL => $newURL) {
				if (substr($oldURL, -1) != '/') {
					$oldURL .= '/';
				}
				$oldURL = addcslashes($oldURL, "/");
				$newURL = addcslashes($newURL, "/");

				foreach ($nls_list as $nls => $nls_name) {
					if ($this->data->$nls) {
						$nls_fields = array( 'page' , 'summary' );
						foreach ($nls_fields as $nls_field) {
							$page = $this->data->$nls->$nls_field;
							if ($page && is_string($page)) {
								$regexp = '|' . str_replace('|', '\|', str_replace("\\", "\\\\", $oldURL)) . '|';

								$parts = explode("arargs:args=", $page);
								foreach ($parts as $part) {
									$part = "arargs:args=" . $part;
									$count = preg_match("/arargs:args=\"(.*?)\"/i", $part, $matches);
									$oldargs = $matches[1];
									$oldargs = base64_decode(urldecode($oldargs));
									$newargs = preg_replace($regexp, $newURL, $oldargs);
									$page = str_replace($matches[1], base64_encode($newargs), $page);
								}

								if ($page != $this->data->$nls->$nls_field) {
									$this->data->$nls->$nls_field = $page;
									$dosave = true;
								}
							}
						}
					}
					if (is_array($this->data->custom[ $nls ])) {
						foreach ($this->data->custom[ $nls ] as $customField => $page) {
							if ($page && is_string($page)) {
								$regexp = '|' . str_replace('|', '\|', str_replace("\\", "\\\\", $oldURL)) . '|';

								$parts = explode("arargs:args=", $page);
								foreach ($parts as $part) {
									$part = "arargs:args=" . $part;
									$count = preg_match("/arargs:args=\"(.*?)\"/i", $part, $matches);
									$oldargs = $matches[1];
									$oldargs = base64_decode(urldecode($oldargs));
									$newargs = preg_replace($regexp, $newURL, $oldargs);
									$page = str_replace($matches[1], base64_encode($newargs), $page);
								}


								if ($page != $this->data->custom[$nls][$customField]) {
									$this->data->custom[$nls][$customField] = $page;
									$dosave = true;
								}
							}
						}
					}
				}
			}

			if ($dosave) {
				$this->save();
			}
		}
	}
?>