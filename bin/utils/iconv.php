<?php
	$charset = "CP1252";
	$table_start = 0x0;
	$table_end = 0xff;

	echo "<?php\n";
	echo "	\$table_start = $table_start;\n";
	echo "	\$table_end = $table_end;\n";

	for ($c = $table_start; $c <= $table_end; $c++) {
		$tstr = iconv($charset, "UTF-8", chr($c));
		echo "	\$table[$c] = \"";
		for ($i = 0; $i < strlen($tstr); $i++) {
			echo  "\\x".dechex(ord($tstr[$i]));
		}
		echo "\";\n";
	}
	echo "?>";
?>
