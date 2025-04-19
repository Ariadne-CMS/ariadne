<?php
	$code		= $store->get_config("code");
	$templates = $store->get_config("files")."templates/";
	$templateStore = $store->get_filestore("templates");

	require_once( $code . "modules/mod_pinp.phtml" );
	require_once( $code . "/ar/beta/diff.php" );
	require_once( $code . "/includes/diff/DiffEngine.php" );
	require_once( $code . "/includes/diff/ariadne.diff.inc" );

	function getDiff( $f, $data ) {
		$pipes = [];
		$proc = proc_open( "/usr/bin/diff -u $f -", [ [ "pipe", "r" ], [ "pipe", "w" ], [ "pipe", "w" ] ], $pipes );
		if ( !is_resource( $proc ) ) {
			die( "Could not open php!\n" );
		}
		fwrite( $pipes[ 0 ], $data );
		fclose( $pipes[ 0 ] );
		$stdout = stream_get_contents( $pipes[ 1 ] );
		$stderr = stream_get_contents( $pipes[ 2 ] );
		fclose( $pipes[ 1 ] );
		fclose( $pipes[ 2 ] );
		$retVal = proc_close( $proc );
		return $stdout;
	}

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


	function compileTemplate( $f, &$error ) {
		global $AR,$templateStore;
		$compiled = null;
		if (substr($f, -strlen(".pinp")) == ".pinp" && is_file($f)) {
			$templateName =  substr($f, 1, -strlen(".pinp"));
//			echo "Compiling $templateName<br>\n";
			$pinp_code = file_get_contents($f);

			$compiler = new pinp($AR->PINP_Functions, "local->", "\$AR_this->_");
			$optimized = sprintf($AR->PINPtemplate, $compiled ?? "");
			$compiled = $compiler->compile(strtr($pinp_code, "\r", ""));
			if ($compiler->error) {
				showCompilerError($compiler, $f);
				$error = $compiler->error;
			}
		} else {
			echo "$f is to a pinp template or does not exist.\n";
		}
		return $compiled;
	}

//	recompile($templates);

//	echo "Done with compiling all PINP templates.<br>\n";
?>
