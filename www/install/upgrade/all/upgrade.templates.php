<?php
	$code		= $store->get_config("code");
	$templates = $store->get_config("files")."templates/";
   $templateStore = $store->get_filestore("templates");

	require_once($code."modules/mod_pinp.phtml");

	function showWithLineNumber($text) {
		$textarray = explode("\n",$text);
		$i=1;
		$result = '';

		foreach($textarray as $line){
			$result .= sprintf("%4d: %s\n",$i++,$line);
		}

		return $result;
	}

	function pathToObjectID($path) {
		global $templates;
		$objectID = 0;
		$subpath = substr($path,strlen($templates));
		$numbers = explode('/',$subpath);;
		while (count($numbers)){
			$pathicle = array_pop($numbers);
			#print "objectID  == ".$objectID." and pathicle == ".$pathicle."\n";
			$objectID = $objectID * 100;
			$objectID += (int)$pathicle;
		}

		#print "End Result: ".$objectID."\n";
		return $objectID;
	}

	function showCompilerError($compiler, $pinp_template) {
		echo "\n------------------\n";
		echo "Error in '$pinp_template': ".$compiler->error."\n";
		echo "------------------\n\n\n";
		echo showWithLineNumber($compiler->scanner->YYBUFFER)."\n\n\n";
		echo "------------------\n";
		echo "in_pinp:           ".$compiler->in_pinp."\n";
		echo "token_ahead:       ".$compiler->token_ahead."\n";
		echo "token_ahead_value: ".$compiler->token_ahead_value."\n";
		echo "token:             ".$compiler->token."\n";
		echo "token_value:       ".$compiler->token_value."\n";
		echo "YYLINE:            ".$compiler->scanner->YYLINE."\n";
		echo "YYCURSOR:          ".$compiler->scanner->YYCURSOR."\n";
		echo "YYSTATE:           ".$compiler->scanner->YYSTATE."\n";
		echo "------------------\n\n";
	}


	function recompile($path) {
		global $AR,$templateStore;
		$dh = opendir($path);
		$objectID = pathToObjectID($path);
		while ( false !== ($file = readdir($dh))) {
			if ($file != "." && $file != "..") {
				$f = $path.$file;
				if (substr($file, -strlen(".pinp")) == ".pinp" && is_file($f)) {
					$templateName =  substr($file, 1, -strlen(".pinp"));
					echo "Recompiling $templateName<br>\n";
					$pinp_code = file_get_contents($f);

					$compiler = new pinp($AR->PINP_Functions, "local->", "\$AR_this->_");
					$pinp_code_compiled_new = $compiler->compile(strtr($pinp_code, "\r", ""));
					if ($compiler->error) {
						showCompilerError($compiler, $path.$file);
					} else {
						$templateStore->write($pinp_code_compiled_new,$objectID,$templateName);
						$templateCode = $templateStore->templateCodeFunction($objectID, $templateName);
						$optimized = '<?php $arTemplateFunction = function(&$AR_this) { '.$templateCode.' }; ?>';
						$templateStore->write($optimized, $objectID, $templateName.".inc");
					}
				} else if (is_dir($f) && $file != "CVS" && $file != ".svn") {
					recompile("$f/");
				}
			}
		}
		closedir($dh);
	}

	recompile($templates);

	echo "Done with recompiling all PINP templates.<br>\n";
?>