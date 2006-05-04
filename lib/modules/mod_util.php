<?php
	require_once($this->store->get_config('code').'modules/mod_pinp.phtml');

	class util {

		function path_unescape($path) {
			$result = "";
			if ($path) {
				debug("path_unescape: escaped path: $path");
				$result = preg_replace_callback(
					'/(_[0-9a-fA-F][0-9a-fA-F]|__)/',
					create_function(
						'$matches',
						// Two types of escaped characters can be here, the
						// underscore or other characters. Check for the
						// underscore first.

						'$char = $matches[0];'.
						'if ($char[1] == "_") {'.
						// It is the underscore, return it as a character.
						'       return "_";'.
						'}'.

						// Assume it is an escaped character here. Find the
						// numbers in hex, turn them back to decimal, get
						// the corresponding character and return it.

						'return chr(hexdec(substr($char, 1, 2)));'
					),
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
					create_function(
						// Replaces characters in the path with their number.
						// Quite similar to " " -> "%20" for HTML escape, but we use _ instead of %
						// This function is to be used as a callback for preg_replace_callback
						'$char',
						'if ($char[0]) {'.
						'       if ($char[0]=="_") {'.
						'	       return "__"; '.
						'       } else {'.
						'	       return "_".dechex(ord($char[0]));'.
						'       }'.
						'}'
					),
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
			$pinp = new pinp("header", 'var_', '$this->_');
			$safe_args = substr($pinp->compile("<pinp>$args</pinp>"), 5, -2);
			$pinp = new pinp("header", 'var_', '$this->_');
			$safe_code = substr($pinp->compile("<pinp>$code</pinp>"), 5, -2);
			return create_function($safe_args, $safe_code);
		}

		function _call_function($callback, $a=null, $b=null, $c=null, $d=null, $e=null) {
			$result = null;
			if (pinp_util::is_callback($callback)) {
				switch (true) {
					case $a === NULL: $result = $callback(); break;
					case $b === NULL: $result = $callback($a); break;
					case $c === NULL: $result = $callback($a, $b); break;
					case $d === NULL: $result = $callback($a, $b, $c); break;
					case $e === NULL: $result = $callback($a, $b, $c, $d); break;
					default:          $result = $callback($a, $b, $c, $d, $e); break;
				}
			} else {
				$this->error = "'$callback' is not a callback function";
			}
			return $result;
		}

		function _usort(&$array, $callback) {
			$result = false;
			if (pinp_util::is_callback($callback)) {
				$result =  usort($array, $callback);
			} else {
				$this->error = "'$callback' is not a valid callback function";
			}
		}

		function _path_escape($path) {
			return parent::path_escape($path);
		}

		function _path_unescape($path) {
			return parent::path_unescape($path);
		}

	}
?>