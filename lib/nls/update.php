#!/usr/bin/php5 -q
<?php

	function debug() {
		// fake debug funtion used in nls files
	}

	$languages=Array('nl','de','es','pl','hr','it','fr','sv', 'en');
	$modules=Array('','ariadne.');
	$target='./';
	define('NLSESCAPE', "\"\\\n\r");

	foreach ($modules as $module){
		include($module."en");
		$default=$ARnls;
		ksort($default);

		unset($ARnls);

		foreach ($languages as $language) {
			echo "Updating ".$module.$language."\n";
			include($module.$language);

			$content = "<"."?php\n\n";
			if ( file_exists($module.$language.".head") ) {
				$content .= file_get_contents($module.$language.".head");
			}
			foreach ($default as $arkey => $arvalue ) {
				$tabs=substr("								",(int)((strlen($arkey)+2)/4));
				if (isset($ARnls[$arkey])) {
					$content .= sprintf("\t%-47s =    \"%s\";\n", "\$ARnls[\"$arkey\"]", AddCSlashes(stripcslashes($ARnls[$arkey]), NLSESCAPE));
				} else {
					$content .= sprintf("//\t%-47s =    \"%s\"; // value from english\n", "\$ARnls[\"$arkey\"]", AddCSlashes(stripcslashes($arvalue), NLSESCAPE));
				}
			}
			unset($ARnls);
			$content .= "\n\n?".">";

			$filename=$target.$module.$language;
			// write file
			$result = file_put_contents($filename,$content);
			if ( $result === false ) {
				die("ERROR: couldn't open $filename for writing.\n");
			}

		}
	}

?>