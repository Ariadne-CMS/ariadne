<?php
	/**
	 * Ariadne javascript loader
	 * 
	 * this template loads a list of javascript files from the ariadne/www/js/ folder
	 * it allows you to load multiple javascript files in one request
	 * 
	 * usage: 
	 * <script src="ariadne.load.js?muze.js+muze.util.js"></script>
	 *
	 * <pinp> call("ariadne.load.js", array("files" => array("muze", "muze.util.js")); </pinp>
	 *
	 * if the requested file isn't found in the js folder, the loader will replace '.' with '/' characters,
	 * starting at the left and try again.
	 **/

	// FIXME: client side caching aan
	// FIXME: ook een packed versie van de javascript neerzetten
	// FIXME: ook een debug.js of loadsource.js ofzo, die niet de packed versie neemt en geen client side cache zet

	if (!$ARCurrent->arDontCache && $ARCurrent->cachetime!=-1 ) {
		$ARCurrent->cachetime=-2; // set cache to onchange
	}
	ldSetContent('text/javascript');
	
	$docroot = $AR->dir->root.'js/';
	if (!is_array($files)) {
		$files = explode('+', $_SERVER['QUERY_STRING']);
	}
	if (is_array($files)) {
		foreach($files as $file) {
			$re = '|[^a-z-_.0-9/]*|i';				// only allow 'normal' characters
			$file = str_replace('//', '/', 			// protects against urls in the form of //google.com
					str_replace('..', '', 			// protects against ../../../../../etc/passwd
					preg_replace($re, '', $file))); // add .js if not set, remove .. and other dirty characters
			if (substr($file, -3)!='.js') {
				$file .= '.js';
			}

			if (file_exists($docroot.$file)) {
				readfile($docroot.$file);
			} else {
				$newfile = $file;
				do {
					$pos = strpos($newfile, '.');
					if ($pos !== false) {
						$temp = substr_replace($newfile, '/', $pos, 1);
					} else {
						$notfound[]=$file;
						break;
					}
					$newfile = $temp;
				} while (!file_exists($docroot.$newfile));
				if (file_exists($docroot.$newfile)) {
					readfile($docroot.$newfile);
				}
			}
		}
		if (is_array($notfound)) {
			$ARCurrent->arDontCache = 1;
			global $nocache;
			$nocache = true;
			echo "\n\n/************ NOT FOUND ******************\n";
			foreach($notfound as $file) {
				echo "  $file\n";
			}
			echo "*****************************************/";
		}
	}
?>
