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

	function esiRemove($page) {
		$regExp = '|<esi:remove>(.*)</esi:remove>|Uis';
		return preg_replace($regExp, "", $page);
	}

	function esiComment($page) {
		$regExp = '|<esi:comment[^>]*>|Uis';
		return preg_replace($regExp, "", $page);
	}

	function esiMarker($page) {
		$regExp = '|<!--esi(.*)-->|Uis';
		return preg_replace($regExp, '$1', $page);
	}

	function esiVars($page) {
		$regExp = '|<esi:vars>(.*)</esi:vars>|Uis';
		$page = preg_replace_callback($regExp, function($matches){
			return ESI::esiExpression($matches[1]);
		}, $page);
		return $page;
	}

	function esiInclude($page) {
		/* TODO:
			alt
			onerror
		*/
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

	function esiProcess($page) {
		/*
			TODO:
				inline
				choose/when/otherwise
				try/attempt/except

				[v] include
				[v] comment
				[v] remove
				[v] vars
				[v] <!-- esi -->
		*/

		$page = ESI::esiMarker($page);
		$page = ESI::esiInclude($page);
		$page = ESI::esiVars($page);
		$page = ESI::esiRemove($page);
		$page = ESI::esiComment($page);

		return $page;
	}
}
?>
