<?php
	/* code by lars(at)ioflux.net, reproduced from www.php.net */
	/* todo: add a list of common unicode characters (&euro, etc.) */

	class unicode {

		function utf8convert($string, $maxchar=0x7F, $entities=true) {

			$returns = "";
			$UTF8len = array(	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
								1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0,
								0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 2, 2, 2, 2, 2,
								2, 2, 3, 3, 3, 3, 4, 4, 5, 6);
			$pos = 0;
			$total = strlen($string);

			do {
				$c = ord($string[$pos]);
				$len = $UTF8len[($c >> 2) & 0x3F];
				switch ($len)
				{
					case 6:
						$u = $c & 0x01;
						break;
					case 5:
						$u = $c & 0x03;
						break;
					case 4:
						$u = $c & 0x07;
						break;
					case 3:
						$u = $c & 0x0F;
						break;
					case 2:
						$u = $c & 0x1F;
						break;
					case 1:
						$u = $c & 0x7F;
						break;
					case 0:	/* unexpected start of a new character */
						$u = $c & 0x3F;
						$len = 5;
						break;
				}
				while (--$len && (++$pos < $total && $c = ord($string[$pos]))) {
					if (($c & 0xC0) == 0x80) {
						$u = ($u << 6) | ($c & 0x3F);
					} else {
						/* unexpected start of a new character */
						$pos--;
						break;
					}
				}
				if ($u <= $maxchar) {
					$returns .= chr($u);
				} else if ($entities) {
					$returns .= '&#'.$u.';';
				} else {
					$returns .= '?';
				}
			} while (++$pos < $total);
			return $returns;
		}

		function utf8toiso8859($string, $entities=true) {
			return unicode::utf8convert($string, 0xFF, $entities);
		}


		function convertToUTF8($charset, $string) {
		global $charset_table;
			if (!$charset_table[$charset]) {
				$tablename = preg_replace('/[^a-z0-9_-]*/', '', strtolower($charset));
				include("mod_unicode.$tablename.php");
				$charset_table[$charset] = $table;
			}
			if (is_string($string)) {
				$result = "";
				for ($i=0; $i<strlen($string); $i++) {
					$result .= $charset_table[$charset][ord($string[$i])];
				}
			} else {
				$result = $string;
			}
			return $result;
		}
	}

	class pinp_unicode extends unicode {

		function _utf8convert($string, $maxchar=0x7F, $entities=true) {
			return pinp_unicode::utf8convert($string, $maxchar, $entities);
		}

		function _utf8toiso8859($string, $entities=true) {
			return pinp_unicode::utf8toiso8859($string, $entities);
		}

		function _convertToUTF8($charset, $string) {
			return pinp_unicode::convertToUTF8($charset, $string);
		}

	}

?>