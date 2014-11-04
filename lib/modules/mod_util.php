<?php
	require_once($this->store->get_config('code').'modules/mod_pinp.phtml');

	class util {
		function getFileFromFTP($url, $fileName) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			require_once($me->store->get_config("code")."modules/mod_mimemagic.php");
			if (!$filename) {
				$filename = basename($url);
			}

			$result = false;
			preg_match('|([^:]+):([^@]+)@([^/]+).*$|i', $url,$matches);

			$file_artemp =tempnam($me->store->get_config("files")."temp","upload");

			$ftpId = ftp_connect($matches[3]);
			ftp_login($ftpId, $matches[1], $matches[2]);
			ftp_get($ftpId, $file_artemp, $fileName, FTP_BINARY);

			readfile($file_artemp);

			return $result;
		}

		function path_unescape($path) {
			$result = "";
			if ($path) {
				debug("path_unescape: escaped path: $path");
				$result = preg_replace_callback(
					'/(_[0-9a-fA-F][0-9a-fA-F]|__)/',
					function( $matches ) {
						// Two types of escaped characters can be here, the
						// underscore or other characters. Check for the
						// underscore first.

						$char = $matches[0];
						if ($char[1] == "_") {
						// It is the underscore, return it as a character.
						       return "_";
						}

						// Assume it is an escaped character here. Find the
						// numbers in hex, turn them back to decimal, get
						// the corresponding character and return it.

						return chr(hexdec(substr($char, 1, 2)));
					},
					$path
				);
			}
			debug("path_unescape: unescaped path: $result");
			return $result;
		}


		function path_escape($path) {
			// This function will return an escaped path. All the characters not supported by Ariadne will be encoded.
			// See also path_escape_callback

			// Returns an empty string if no path, or an empty path was given.
			$result = "";
			if ($path) {
				debug("path_escape:files unescaped path: $path");
				$result = preg_replace_callback(
					'/[^\/A-Za-z0-9.-]/',
					function ( $char ) {
						// Replaces characters in the path with their number.
						// Quite similar to " " -> "%20" for HTML escape, but we use _ instead of %
						// This function is to be used as a callback for preg_replace_callback
						if ($char[0]) {
							if ($char[0]=="_") {
								return "__";
							} else {
								return "_".dechex(ord($char[0]));
							}
						}
					},
					$path
				);
			}
			debug("path_escaspe:files escaped path: $result");
			return $result;
		}
	}

	class pinp_util extends util {

		function is_callback($callback) {
			// lambda functions do begin with a null character
			// maybe there is a better check, but this will do it for now
			$result =  ($callback[0] === "\000" && substr($callback, 1, strlen('lambda_')) == 'lambda_');
			return $result;
		}


		function _create_function($args, $code) {
		global $AR;
			$pinp = new pinp($AR->PINP_Functions, 'var_', '$AR_this->_');
			$safe_args = $pinp->compileFuncCallArgs("$args", "funcCallArgs");
			$pinp = new pinp($AR->PINP_Functions, 'var_', '$AR_this->_');
			$safe_code = substr($pinp->compile("<pinp>$code</pinp>"), 5, -2);
			return create_function($safe_args, $safe_code);
		}

		function _call_function($callback) {
			$args = array_slice(func_get_args(), 1);
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$result = null;
			if (pinp_util::is_callback($callback)) {
				$result = call_user_func_array($callback, $args);
			} else {
				$me->error = "'$callback' is not a callback function";
			}
			return $result;
		}

		function _preg_replace_callback($regExp,$callback,$haystack) {
				$context = pobject::getContext();
				$me = $context["arCurrentObject"];
				$result = false;
				if (pinp_util::is_callback($callback)) {
						$result =  preg_replace_callback($regExp, $callback,$haystack);
				} else {
						$me->error = "'$callback' is not a valid callback function";
				}
				return $result;
		}


		function _usort(&$array, $callback) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$result = false;
			if (pinp_util::is_callback($callback)) {
				$result =  usort($array, $callback);
			} else {
				$me->error = "'$callback' is not a valid callback function";
			}
			return $result;
		}

		function _uasort(&$array, $callback) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$result = false;
			if (pinp_util::is_callback($callback)) {
				$result =  uasort($array, $callback);
			} else {
				$me->error = "'$callback' is not a valid callback function";
			}
			return $result;
		}

		function _path_escape($path) {
			return parent::path_escape($path);
		}

		function _path_unescape($path) {
			return parent::path_unescape($path);
		}

		function _getFileFromFTP($url, $fileName) {
			return parent::getFileFromFTP($url, $fileName);
		}

	}
?>