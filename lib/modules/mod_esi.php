<?php

class ESI {
	function esiExpression( $expression ) {
		/*  Regexp now matches $(HTTP_COOKIE{stuff});
			TODO:
			Add matching for:
				[v] $(HTTP_COOKIE{stuff}|default)
				[v] $(HTTP_COOKIE{stuff}|'default blah')

			Add variable replacement for:
				HTTP_ACCEPT_LANGUAGE
				HTTP_HOST
				HTTP_REFERER
				HTTP_USER_AGENT
				QUERY_STRING

			FIXME: ariadne cookies are serialized by default, which would break HTTP_COOKIE usage in other ESI processors 
		*/

		$result = preg_replace_callback('!\$\(([^){]*)(\{(([^}]*))\}(\|([^)]*))?)\)!', function($matches) {
					// print_r($matches);
					switch ($matches[1]) {
						case 'HTTP_COOKIE':
							$cookie = ldGetUserCookie($matches[3]);
							$default = preg_replace("/^'(.*?)'$/", "$1", $matches[6]);
							return $cookie ? $cookie : $default;
						break;
					}
		}, $expression);
		return $result;
	}

	function esiInclude($page) {
		global $ARCurrent, $AR;

		// parse <esi:include src="view.html">
		$regExp = '|<esi:include.*?>|i';

		require_once('mod_htmlparser.php');

		preg_match_all($regExp, $page, $matches);

		foreach ($matches[0] as $match) {
			$parts = htmlparser::parse($match);

			$src = $parts['children'][0]['attribs']['src'];
			$src = ESI::esiExpression( $src );
			if (strstr($src, $_SERVER['SCRIPT_NAME'])) {
				// Looks like an Ariadne request, handle it!
				$urlArr = parse_url($src);
				parse_str($urlArr['query'], $_GET);
				$pathInfo = str_replace($_SERVER['SCRIPT_NAME'], '', $urlArr['path']);

				ob_start();
					ldProcessRequest($pathInfo);
					$output = ob_get_contents();
				ob_end_clean();
				$page = str_replace($match, $output, $page);
			} else {
				// FIXME: Is it a good idea to do http requests from the server this way?
				$result = ar('http')->get($src);
				$page = str_replace($match, $result, $page);
			}
		}

		return $page;
	}
}
?>
