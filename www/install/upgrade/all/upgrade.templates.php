<?php
	$code		= $store->config["code"];
	$templates 	= $store->config["files"]."templates/";
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
		$php_version = (int)substr(phpversion(), 0, 1);
		$dh = opendir($path);
		while ( false !== ($file = readdir($dh))) {
			if ($file != "." && $file != "..") {
				$f = $path.$file;
				if (substr($file, -strlen(".pinp")) == ".pinp" && is_file($f)) {
					$file_compiled =  substr($f, 0, -strlen(".pinp"));
					echo "Recompiling $file<br>\n";
					$pinp_code = "";
					$fp = fopen($f, "r");
					while (!feof($fp)) {
						$pinp_code .= fread($fp, 4096);
					}
					fclose($fp);

					if ($php_version > 4) {
						$objectContext = "\$AR_this->_";
					} else {
						$objectContext = "\$this->_";
					}
					$compiler = new pinp("head", "local->", $objectContext);
					$pinp_code_compiled_new = $compiler->compile(strtr($pinp_code, "\r", ""));
					if ($compiler->error) {
						showCompilerError($compiler, $path.$file);
					} else {
						$fp = fopen($file_compiled, "w");
						fwrite($fp, $pinp_code_compiled_new);
						fclose($fp);
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