#!/usr/bin/php -q
<?php
	// location of the gnome-libs mime-magic file
	$magic_db="/etc/mime-magic";

	// start output buffering
	ob_start();
	echo "<?php \n"; // start php template

	$mfp=fopen($magic_db, "r");
	if ($mfp) {
		// offset:
		//	regs[1]: contains number
		//  regs[2]: contains right side of hex number (regs[1] left side)
		$re='^([0-9]+(x[0-9]+)?)[[:space:]]+';

		// type:
		$re.='([a-z]+)[[:space:]]+';

		// data:
		$re.='(([\\].|[^[:space:]])+)[[:space:]]+';

		// mime-type:
		$re.='(.*)$';

		while ($line=fgets($mfp, 1000)) {
			if (ereg($re, $line, $regs)) {
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
							if (ereg('^[0-9]{3}', substr($data, $esc+1, 3), $regs)) {
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
				if (eregi('^[a-z0-9_-]+/[a-z0-9_-]+$', $mimetype, $regs)) {
					echo "	\$mimemagic_data[$offset][$len][\"";
					for ($i=0; $i<strlen($data); $i++) {
						echo "\\".decoct(ord($data[$i]));
					}
					echo "\"]=\"".AddSlashes($mimetype)."\"; \n";
				}
				$len=0;
			}
		}
		fclose($mfp);

	?>

	function get_mime_type($filename) {
	global $mimemagic_data;
		$result = false;
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