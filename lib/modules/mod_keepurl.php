<?php
	class pinp_keepurl {

		function _make_path($path=".") {
		global $ARCurrent;
			$path = $this->make_path($path);

			$redirects = $ARCurrent->shortcut_redirect;
			if (is_array($redirects)) {
				$newpath = $path;
				$c_redirects = count($redirects);
				$c_redirects_done = 0;
				while (count($redirects) && ($redir = array_pop($redirects)) && substr($newpath, 0, strlen($redir['dest'])) == $redir['dest']) {
					$c_redirects_done++;
					$newpath = $redir['src'].substr($newpath, strlen($redir['dest']));
				}
			}

			return $newpath;
		}

		function _currentsection($path=".") {
		global $ARCurrent;
			$path = $this->make_path($path);
			if (@count($ARCurrent->shortcut_redirect)) {
				$redir = end($ARCurrent->shortcut_redirect);
				if ($redir["keepurl"] && substr($path, 0, strlen($redir["dest"])) == $redir["dest"]) {
					$path = $redir["src"];
				}
			}
			$config=$this->loadConfig($path);
			return $config->section;
		}

		function _get($rpath, $template, $args='') {
		global $ARCurrent;
			$rpath = $this->make_path($rpath);
			$path = $rpath;
			while ($path != $prevPath && !$this->exists($path)) {
				$prevPath = $path;
				$path = $this->store->make_path($path, '..');
			}
			if ($path != $rpath) {
				$shortcut = current($this->get($path, 'system.get.phtml'));
				if (!$shortcut->implements('pshortcut')) {
					$result = Array();
				} else {
					if (!is_array($ARCurrent->shortcut_redirect)) {
						$ARCurrent->shortcut_redirect = Array();
					}
					array_push($ARCurrent->shortcut_redirect, Array("src" => $path, "dest" => $shortcut->data->path, "keepurl" => $shortcut->data->keepurl));
						$npath = $shortcut->data->path.substr($rpath, strlen($path));
						$result = $this->get($npath, $template, $args);
					array_pop($ARCurrent->shortcut_redirect);
				}

			} else {
				$result = $this->get($path, $template, $args);
			}

			return $result;
		}

	}
?>