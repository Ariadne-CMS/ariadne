<?php

	class pinp_keepurl {

		function _make_path($path=".") {
		global $ARCurrent;
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$path = $me->make_path($path);
			$newpath = $path;
			$redirects = $ARCurrent->shortcut_redirect;
			if (is_array($redirects)) {
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
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$path = $me->make_path($path);
			if (@count($ARCurrent->shortcut_redirect)) {
				$redir = end($ARCurrent->shortcut_redirect);
				if ($redir["keepurl"] && substr($path, 0, strlen($redir["dest"])) == $redir["dest"]) {
					$path = $redir["src"];
				}
			}
			$config=$me->loadConfig($path);
			return $config->section;
		}

		function _get($rpath, $template, $args='') {
		global $ARCurrent;
			// for now we have to remove all current redirects
			$old_redirects = $ARCurrent->shortcut_redirect;
			$ARCurrent->shortcut_redirect = Array();
			$result = pinp_keepurl::getWorker($rpath, $template, $args);
			// restore redirects
			$ARCurrent->shortcut_redirect = $old_redirects;
			return $result;
		}

		function getWorker($rpath, $template, $args='') {
		global $ARCurrent;
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];

			$rpath = $me->make_path($rpath);
			$path = $rpath;
			while ($path != $prevPath && !$me->exists($path)) {
				$prevPath = $path;
				$path = $me->store->make_path($path, '..');
			}
			if ($path != $rpath) {
				$shortcut = current($me->get($path, 'system.get.phtml'));
				if (!$shortcut->AR_implements('pshortcut')) {
					$result = Array();
				} else {
					if (!is_array($ARCurrent->shortcut_redirect)) {
						$ARCurrent->shortcut_redirect = Array();
					}
					$subpath = substr($rpath, strlen($path));
					$target = $shortcut->call('system.get.target.phtml');
					array_push($ARCurrent->shortcut_redirect, Array("src" => $path, "dest" => $target, "keepurl" => $shortcut->data->keepurl));
					if ($me->exists($target.$subpath)) {
						$result = $me->get($target.$subpath, $template, $args);
					} else {
						$result = pinp_keepurl::getWorker($target.$subpath, $template, $args);
					}
					array_pop($ARCurrent->shortcut_redirect);
				}

			} else {
				$result = $me->get($path, $template, $args);
			}
			return $result;
		}

	}
?>
