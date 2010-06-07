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
				while (count($redirects) && ($redir = array_pop($redirects)) && substr($newpath, 0, strlen($redir['dest'])) == $redir['dest'] && $redir['keepurl']) {
					$c_redirects_done++;
					$newpath = $redir['src'].substr($newpath, strlen($redir['dest']));
				}
			}

			return $newpath;
		}

		function _loadConfig($path='.') {
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
			return $config;
		}
		
		function _currentsection($path=".") {
			$config = self::_loadConfig($path);
			return $config->section;
		}

		function _currentsite($path=".") {
			$config = self::_loadConfig($path);
			return $config->site;
		}

		function _get($path, $template, $args='') {
		global $ARCurrent;
			// for now we have to remove all current redirects
			$old_redirects = $ARCurrent->shortcut_redirect;
			$ARCurrent->shortcut_redirect = Array();
			$realpath = self::_make_real_path($path);
			if ($realpath) {
				$context = pobject::getContext();
				$me = $context["arCurrentObject"];
				$result = $me->get( $realpath, $template, $args );
			} else {
				$result = Array();
			}
			// restore redirects
			$ARCurrent->shortcut_redirect = $old_redirects;
			return $result;
		}

		function _make_real_path($path) {
			global $ARCurrent;
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];

			$path = $me->make_path($path);
			$originalPath = $path;
			while ($path != $prevPath && !$me->exists($path)) {
				$prevPath = $path;
				$path = $me->make_path($path.'../');
			}
			if ($path != $originalPath) {
				$shortcut = current($me->get($path, 'system.get.phtml'));
				if (!$shortcut->AR_implements('pshortcut')) {
					$result = false;
				} else {
					if (!is_array($ARCurrent->shortcut_redirect)) {
						$ARCurrent->shortcut_redirect = Array();
					}
					$subpath = substr($originalPath, strlen($path));
					$target = $shortcut->call('system.get.target.phtml');
					array_push($ARCurrent->shortcut_redirect, Array("src" => $path, "dest" => $target, "keepurl" => $shortcut->data->keepurl));
					if ($me->exists($target.$subpath)) {
						$result = $target.$subpath;
					} else {
						$result = self::_make_real_path($target.$subpath);
					}
					array_pop($ARCurrent->shortcut_redirect);
				}

			} else {
				$result = $path;
			}
			return $result;
		}

	}
?>
