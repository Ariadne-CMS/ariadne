<?php

class URL {

	function arguments($args, $prefix='') {
		if (!is_array($args)) return '';
		$str = '';
		foreach ($args as $key => $value) {
			if ($str !== '') $str.='&';
			$fullkey = ($prefix === '') ? $key : $prefix.'['.$key.']';
			$str .= is_array($value) ? $this->_query_str($value, $fullkey) : $fullkey.'='.rawurlencode($value);
		}
		if ($prefix == '' && $str !== '') $str = '?' . $str;
		return $str;
	}

	/* replaces the URLs with the {ar*[/nls]} markers */
	function RAWtoAR($page, $nls="") {
		global $ARCurrent, $AR;

		$nls_match = "(/(?:".implode('|', array_keys($AR->nls->list))."))?";
		/* find and replace the current page */
		$find[] = "%\\Q".$this->make_url($this->path, "\\E{0}(".$nls_match.")\\Q")."\\E%"; $repl[] = "{arCurrentPage\\1}";
		$find[] = "%".preg_replace("%^https?://%", "https?\\Q://", $AR->host).$AR->dir->www."loader.php\\E(?:/-".$ARCurrent->session->id."-)?".$nls_match."\\Q".$this->path."\\E%"; $repl[] = "{arCurrentPage\\1}";

		// change the site links
		$site = $this->currentsite();
		if ($site && $site !== '/') {
			$find[] = "%\\Q".$this->make_url($site, "\\E{0}(".$nls_match.")?\\Q")."\\E?%"; 
echo "frip: $site<br>\n";
			$repl[] = "{arSite\\1}";
		}

		// change hardcoded links and images to use a placeholder for the root
		if ($this->store->root) {
			$root = $this->store->root;
			if (substr($root, -3) == "/$nls") {
				$root = substr($root, 0, -3);
			}
			$find[] = "%(http[s]?://)?\\Q".$AR->host.$root."\\E".$nls_match."%"; 
			$repl[] = "{arBase\\1}";
			$find[] = "%(http[s]?://)?\\Q".$root."\\E".$nls_match."%"; 
			$repl[] = "{arBase\\1}";
		}
		// change hand pasted sources, which may or may not include session id's
		$find[] = "%(https?://)?\\Q".$AR->host.$AR->dir->www."loader.php\\E(/-".$ARCurrent->session->id."-)?".$nls_match."%"; 
		$repl[] = "{arBase\\1}";
		if ($ARCurrent->session && $ARCurrent->session->id) {
			// check for other session id's:
			$find[] = "%/-".$ARCurrent->session->id."-%"; 
			$repl[] = "{arSession}";
		}

		return preg_replace($find, $repl, $page);
	}
	
	/* replaces the {ar*[/nls]} markers with valid URLs; if full is false, returns only the <body> content */
	function ARtoRAW($page, $full=false) {
		global $ARCurrent, $AR;
		if ($ARCurrent->session && $ARCurrent->session->id) {
			$session='/-'.$ARCurrent->session->id.'-';
		} else {
			$session='';
		} 
		$site = $this->currentsite();
		$root = $this->store->root;
		if (substr($root, -3) == "/$this->nls") {
			$root = substr($root, 0, -3);
		}
		if ($site && $site !== '/') {
			$find[] = "%\\{(?:arSite)(?:/([^}]+))?\\}\\Q\\E%e"; $repl[] = "\$this->make_url('$site', '\\1')";
			$find[] = "%\\{(?:arRoot|arBase)(?:/([^}]+))?\\}\\Q".$site."\\E%e"; $repl[] = "\$this->make_url('$site', '\\1')";
		}
		$find[] = "%\\{arBase(/(?:[^}]+))?\\}%"; $repl[] = $AR->host.$root."\\1";
		$find[] = "%\\{arRoot(/(?:[^}]+))?\\}%"; $repl[] = $AR->host.$this->store->root."\\1";
		$find[] = "%\\{arCurrentPage(?:/([^}]+))?\\}%e"; $repl[] = "\$this->make_url('', '\\1')";
		$find[] = "%\\{arSession\\}%"; $repl[] = $session;
		return preg_replace($find, $repl, $page);
	}
	
}

class pinp_URL {

	function _arguments($args) {
		return URL::arguments($args);
	}

	function _RAWtoAR($page, $nls='') {
		return URL::RAWtoAR($page, $nls);
	}

	function _ARtoRAW($page, $full) {
		return URL::ARtoRAW($page, $full);
	}
}

?>