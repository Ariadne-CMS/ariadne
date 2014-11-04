#!/usr/bin/env php
<?php
	// location of the mime-magic file
	$magic_db="/usr/share/file/magic.mime";
	$mimetypes_db="/etc/mime.types";

	// start output buffering
	ob_start();
	echo "<?php \n"; // start php template
	echo "	global \$mimemagic_data;\n";
	echo "	global \$mimetypes_data;\n";
	echo "	global \$contenttypes_data;\n";
	echo "\n";
	echo "	define(\"MIME_EXT\",	1);\n";
	echo "	define(\"MIME_DATA\",	2);\n";
	$mfp=fopen($magic_db, "r");
	if ($mfp) {
		// offset:
		//	regs[1]: contains number
		//  regs[2]: contains right side of hex number (regs[1] left side)
		$re='/^([0-9]+(x[0-9]+)?)[[:space:]]+';

		// type:
		$re.='([a-z]+)[[:space:]]+';

		// data:
		$re.='(([\\].|[^[:space:]])+)[[:space:]]+';

		// mime-type:
		$re.='(.*)$/';

		while ($line=fgets($mfp, 1000)) {
			if (preg_match($re, $line, $regs)) {
				if ($regs[2]) {
					$offset=hexdec($regs[1].$regs[2]);
				} else {
					$offset=$regs[1];
				}
				$data=$regs[4];
				$mimetype=chop($regs[6]);
				switch($regs[3]) {
					case 'string':
						while (($esc=strpos($data, "\\"))!==false) {
							if ($data[$esc+1]==='x') {
								$char=hexdec("0".substr($data, $esc+1, 3));
								$newdata=substr($data, 0, $esc);
								$newdata.=chr($char);
								$newdata.=substr($data, $esc+4);
								$data=$newdata;
							} else
							if (preg_match('/^[0-9]{3}/', substr($data, $esc+1, 3), $regs)) {
								// octal represantation
								$char=octdec(substr($data, $esc+1, 3));
								$newdata=substr($data, 0, $esc);
								$newdata.=chr($char);
								$newdata.=substr($data, $esc+4);
								$data=$newdata;
							} else
							if (is_int($data[$esc+1])) {
								$char=(int)$data[$esc+1];
								$newdata=substr($data, 0, $esc);
								$newdata.=chr($char);
								$newdata.=substr($data, $esc+2);
								$data=$newdata;
							} else {
								$newdata=substr($data, 0, $esc);
								$newdata.=substr($data, $esc+1);
								$data=$newdata;
							}
						}
						$len=strlen($data);
					break;

					case 'byte':
						$len=1;

					case 'short':
					case 'beshort':
					case 'leshort':
						if (!$len) {
							$len=2;
						}
					case 'long':
					case 'belong':
					case 'lelong':
					case 'date':
					case 'bedate':
					case 'ledate':
						if (!$len) {
							$len=4;
						}

						if (substr($data, 0, 2)==='0x') {
							$val=hexdec($data);
						} else
						if ($data[0]==='0') {
							$val=octdec($data);
						} else {
							$val=(int)$data;
						}

						if ($len==1) {
							$newdata=chr($val);
						} else
						if ($len==2) {
							$newdata="  ";
							$newdata[0]=chr($val >> 8);
							$newdata[1]=chr($val & 0x00FF);
						} else {
							$newdata="    ";
							$newdata[0]=chr($val >> (8 * 3));
							$newdata[1]=chr(($val >> (8 * 2)) & 0x000000FF);
							$newdata[2]=chr(($val >> (8 * 1)) & 0x000000FF);
							$newdata[3]=chr($val & 0x000000FF);
						}

						$data=$newdata;
					break;

				}

				// test if it is really a mimetype and not some sort of a vague file description
				if (preg_match('|^[.a-z0-9_-]+/[.a-z0-9_-]+$|i', $mimetype, $regs)) {
					$data_index = "";
					for ($i=0; $i<strlen($data); $i++) {
						$data_index.="\\".decoct(ord($data[$i]));
					}

		//			echo "	\$mimemagic_data[$offset][$len][\"".$data_index;
		//			echo "\"]=\"".AddSlashes($mimetype)."\"; \n";
					if ($len > 0) {
						$mimemagic_data[$offset][$len][$data_index] = $mimetype;
					}
				}
				$len=0;
			}
		}
		fclose($mfp);


		reset($mimemagic_data);
		ksort($mimemagic_data);
		while (list($offset, $len_array)=each($mimemagic_data)) {
			reset($len_array);
			krsort($len_array);
			while (list($len, $data_array)=each($len_array)) {
				reset($data_array);
				ksort($data_array);
				while (list($data, $mimetype)=each($data_array)) {
					echo "	\$mimemagic_data[$offset][$len][\"".$data;
					echo "\"]=\"".AddSlashes($mimetype)."\"; \n";
				}
			}
		}


		$mfp = fopen($mimetypes_db, "r");
		if ($mfp) {
			while ($line=fgets($mfp, 4000)) {
				if (!preg_match('/^[[:space:]]*#/i', $line, $regs)) {
					if (preg_match('/^([^[:space:]]*)(.*)$/i', $line, $regs)) {
						$mimetype = $regs[1];
						$extensions = trim(preg_replace('/[[:space:]]+/', ' ', $regs[2]));
						if ($extensions) {
							$extensions = explode(' ', $extensions);
							reset($extensions);
							while (list(,$extension)=each($extensions)) {
								echo "	\$mimetypes_data[\"$extension\"] = \"$mimetype\";\n";
							}
						}
					}
				}
			}
			fclose($mfp);
		}
	?>

	$contenttypes_data = Array(
		'docx' => array(
			'^application/zip$' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
		),
		'css' => array(
			'^text/.*' => 'text/css'
		)

	);

	function get_mime_type($filename, $flags = 3) {
	global $mimemagic_data, $mimetypes_data;
		$result = false;
		if ($flags & MIME_DATA) {
			if (function_exists('finfo_file')) {
				// php 5.3.0 style
				$finfo = @finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
				if ($finfo) {
					$result = @finfo_file($finfo, $filename);
				}
				finfo_close($finfo);
			}
			if (!$result) {
				reset($mimemagic_data);
				$fp = fopen($filename, "rb");
				if ($fp) {
					while (!$result && (list($offset, $odata)=each($mimemagic_data))) {
						while (!$result && (list($length, $ldata)=each($odata))) {
							fseek($fp, $offset, SEEK_SET);
							$lookup=fread($fp, $length);
							$result=$ldata[$lookup];
						}
					}
					fclose($fp);
				}
			}
		}

		if (($flags & MIME_EXT) && ( (($flags & MIME_DATA) && !$result) || !($flags & MIME_DATA) ) ) {
			if (preg_match('/.*[.]([^.]*)/i', $filename, $regs)) {
				$result = $mimetypes_data[strtolower($regs[1])];
			}
		}
		return $result;
	}

	function get_content_type($mimetype, $extension) {
	global $contenttypes_data;

		$result = $mimetype;

		$ePos = strrpos($extension, '.');
		if ($ePos !== false) {
			$extension = substr($extension, $ePos + 1);
		}

		$result = $contenttypes_data[$extension][$mimetype];
		if (!$result) {
			if (is_array($contenttypes_data[$extension])) {
				foreach ($contenttypes_data[$extension] as $check => $res) {
					if (preg_match("|$check|i", $mimetype)) {
						return $res;
					}
				}
			}
		}
		return $result;
	}

<?php
		echo "?>";

		$contents = ob_get_contents();
		ob_end_clean();

		if ($argv[1]) {
			$fp = fopen($argv[1], "w");
			if ($fp) {
				fwrite($fp, $contents);
				fclose($fp);
			} else {
				$error = "Could not open ".$argv[1]." for writing";
			}
		} else {
			echo $contents."\n";
		}
	} else {
		$error="could not open mime-magic file: $magic_db";
	}

	if ($error) {
		echo "error: $error\n";
	}
?>
