<?php
	$code		= $store->get_config("code");
	$files = $store->get_config("files")."files/";
	$needsUpgrade = array();

	function pathToObjectID($path) {
		global $files;
		$objectID = 0;
		$subpath = substr($path,strlen($files));
		$numbers = explode('/',$subpath);;
		while (count($numbers)){
			$pathicle = array_pop($numbers);
			$objectID = $objectID * 100;
			$objectID += (int)$pathicle;
		}

		return $objectID;
	}

	function parseFile($file) {
		preg_match('/^_((?<nls>[a-z]{2})_)?(?<file>.+)$/',$file,$matches);
		if( $matches['nls'] == '') {
			$matches['nls'] = 'none';
		}
		return $matches;
	}

	function recurse($path) {
		global $AR,$needsUpgrade;
		$dh = opendir($path);
		$files = array();
		$nlsFiles = array();
		$dirs = array();
		$objectID = pathToObjectID($path);
		while ( is_resource($dh) && false !== ($file = readdir($dh))) {
			if ($file != "." && $file != "..") {
				$f = $path.$file;
				if ( is_file($f) && $file[0] == '_' ) {
					$files[]=$file;
				} else if (is_dir($f) && $file != "CVS" && $file != ".svn") {
					$dirs[]=$f."/";
				}
			}
		}
		closedir($dh);
		foreach($files as $file){
			$info = parseFile($file);
			$nlsFiles[$info['file']][$info['nls']]  = $info;
		}
		unset($files);
		foreach($nlsFiles as $basefile => $nlsData) {
			if( count($nlsData)  ){
				$needsUpgrade[$objectID]=''.$objectID;
			}
		}
		unset($nlsFiles);

		foreach($dirs as $dir){
			recurse($dir);
		}
		unset($dirs);
	}

	recurse($files);

	sort($needsUpgrade);
	foreach($needsUpgrade as $objID) {
		print "Searching for $objID\n";
		$result = ar::get('/')->find("id == $objID")->call('system.get.phtml');
		if(count($result) == 1) {
			$obj = current($result);
			print "Upgrading ".$obj->path."\n<br>";
			$obj->call('system.upgrade.filestore.8.4.php');
		}
	}

	echo "Done with converting nls filestore.<br>\n";
?>