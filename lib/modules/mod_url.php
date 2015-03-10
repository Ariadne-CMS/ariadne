<?php

class URL {

	/* replaces the URLs with the {ar*[/nls]} markers */
	public function RAWtoAR($page, $nls="") {
		global $ARCurrent, $AR;
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];
		if(!$me) {
			return $page;
		}

		$nls_match = "(/(?:".implode('|', array_keys($AR->nls->list))."))?";
		// FIXME: make the rest of the code also use the $nls_match2 expression
		// which doesn't match deel1/ as the nlsid 'de'
		$nls_match2 = "((".implode('|', array_keys($AR->nls->list)).")/)?";

		/* find and replace the current page */
		$find[] = "%\\Q".$me->make_url($me->path, "\\E{0}(".$nls_match.")\\Q")."\\E(user.edit.page.html|view.html)?%";
		$repl[] = "{arCurrentPage\\1}";
		$find[] = "%".preg_replace("%^https?://%", "https?\\Q://", $AR->host).$AR->dir->www."loader.php\\E(?:/-".$ARCurrent->session->id."-)?".$nls_match."\\Q".$me->path."\\E(user.edit.page.html|view.html)?%";
		$repl[] = "{arCurrentPage\\1}";
		$find[] = "%\\Q".$me->make_local_url($me->path, "\\E{0}(".$nls_match.")\\Q")."\\E(user.edit.page.html|view.html)?%";
		$repl[] = "{arCurrentPage\\1}";
		// change the site links
		$site = $me->currentsite();
		if ($site && $site !== '/') {
			$siteURL = $me->make_url($site, "");
			$rootURL = $me->make_url("/", "");

			/* use the rootURL to rebuild the site URL */
			$find[] = "%\\Q$rootURL\\E".$nls_match2."\\Q".substr($site, 1)."\\E%e";
			$repl[] = "(\"\${2}\") ? \"{arSite/\\2}\" : \"{arSite}\"";

			/*
				a site has been configured so we can directly place
				the nls_match2 after the siteURL
			*/
			$find[] = "%\\Q$siteURL\\E".$nls_match2."%e";
			$repl[] = "(\"\${2}\") ? \"{arSite/\\2}\" : \"{arSite}\"";
		}

		// change hardcoded links and images to use a placeholder for the root
		if ($me->store->get_config("root")) {
			$root = $me->store->get_config("root");
			if (substr($root, -3) == "/$nls") {
				$root = substr($root, 0, -3);
			}
			$find[] = "%(http[s]?://)?\\Q".$AR->host.$root."\\E".$nls_match."(/)%";
			$repl[] = "{arBase\\2}\\3";

			// This regexp triggers problems if there is no session
			// available (either because the user is not logged in, or the
			// site in configured with hideSessionFromUrl, so the check is
			// added to prevent random /ariadne/loader.php's to be replaced;
			if (!empty($ARCurrent->session->id) && strpos($root, "-" . $ARCurrent->session->id . "-") !== false) {
				$find[] = "%(http[s]?://)?\\Q".$root."\\E".$nls_match."(?)%";
				$repl[] = "{arBase\\2}\\3";
			}
		}

		// change hand pasted sources, which may or may not include session id's
		$find[] = "%(https?://)?\\Q".$AR->host.$AR->dir->www."loader.php\\E(/-".$ARCurrent->session->id."-)?(".$nls_match.")?/%";
		$repl[] = "{arBase\\3}/";
		if ($ARCurrent->session && $ARCurrent->session->id) {
			// check for other session id's:
			$find[] = "%/-[^-]{4}-%";
			$repl[] = "{arSession}";
			//$find[] = "%/-".$ARCurrent->session->id."-%";
			//$repl[] = "{arSession}";
		}

		return preg_replace($find, $repl, $page);
	}

	/* replaces the {ar*[/nls]} markers with valid URLs; if full is false, returns only the <body> content */
	public static function ARtoRAW($page) {
		global $ARCurrent, $AR;
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];
		$find = array();
		$repl = array();

		if ($ARCurrent->session && $ARCurrent->session->id) {
			$session='/-'.$ARCurrent->session->id.'-';
		} else {
			$session='';
		}
		$site = $me->currentsite($me->path, true);
		$root = $me->store->get_config("root");
		if (substr($root, -3) == "/$me->nls") {
			$root = substr($root, 0, -3);
		}
		if ($site && $site !== '/') {
			$find[] = "%\\{(?:arSite)(?:/([^}]+))?\\}\\Q\\E%e";
			$repl[] = "\$me->make_url('$site', '\\1')";

			$find[] = "%\\{(?:arRoot|arBase)(?:/([^}]+))?\\}\\Q".$site."\\E%e";
			$repl[] = "\$me->make_url('$site', '\\1')";
		}
		$find[] = "%\\{arBase(/(?:[^}]+))?\\}%";
		$repl[] = $AR->host.$root."\\1";

		$find[] = "%\\{arRoot(/(?:[^}]+))?\\}%";
		$repl[] = $AR->host.$me->store->get_config("root")."\\1";

		$find[] = "%\\{arCurrentPage(?:/([^}]+))?\\}%e";
		$repl[] = "\$me->make_local_url('', '\\1')";

		$find[] = "%\\{arSession\\}%";
		$repl[] = $session;

		if (class_exists('edit') && edit::getEditMode()) {
			$find[] = "%ar:editable=([^ ]+)%";
			$repl[] = "contentEditable=\\1";
		}

		$page = preg_replace($find, $repl, $page);

		// FIXME: Maybe do not process arCall when ESI is enabled?
		$page = URL::processArCall($page, $full);

		return $page;
	}

	protected static function processArCall($page, $full=false) {
		global $ARCurrent, $AR;
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];

		// parse {arCall:/path/to/object/template.phtml?a=1&b=2}
//		$regExp = '|\{arCall:(/(.*/)*)([^?]+)[?]([^}]*?)}|i';
		$regExp = '|\{arCall:(.*?)\}|i';

		while (preg_match($regExp, $page, $matches)) {
			if( !$settings ) {
				if( !$ARCurrent->arEditorSettings) {
					$settings = $me->call("editor.ini");
				} else {
					$settings = $ARCurrent->arEditorSettings;
				}
			}
			ob_start();
				$parts	= explode("?", substr($matches[0], strlen('{arCall:'), -1), 2);
				$args	= $parts[1];
				$rest	= $parts[0];
				$template = "";
				if (substr($rest, -1) !== '/') {
					$template = basename($rest);
				}
				$path = $me->make_path( substr($rest, 0, -(strlen($template))) );
				if (is_array($settings['arCall'][$template])) {
					$cpaths = $settings['arCall'][$template]['paths'];
					if (is_array($cpaths)) {
						$mayCall = false;
						foreach ($cpaths as $cpath) {
							if (substr($cpath, -1) == '*') {
								$cpath = substr($cpath, 0, -1);
								if (substr($path, 0, strlen($cpath)) == $cpath) {
									$mayCall = true;
									break;
								}
							} else
							if ($path === $cpath) {
								$mayCall = true;
								break;
							}
						}
						if (!$mayCall) {
							error("no permission to call '$template' on '$path'");
						} else {
							$me->get($path, $template, $args);
						}
					} else {
						error("no paths were listed for '$template'");
					}
				} else {
					error("template '$template' is not in white list");
				}
				$output = ob_get_contents();
			ob_end_clean();
			$page = str_replace($matches[0], $output, $page);
		}

		return $page;
	}
}

class pinp_URL {

	public static function _RAWtoAR($page, $nls='') {
		return URL::RAWtoAR($page, $nls);
	}

	public static function _ARtoRAW($page) {
		return URL::ARtoRAW($page);
	}
}
