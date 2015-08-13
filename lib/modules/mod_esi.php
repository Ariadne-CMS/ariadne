<?php
require_once($AR->dir->install."/lib/modules/mod_htmlparser.php");

class ESI {
	function esiExpression( $expression ) {
		/*  Regexp now matches $(HTTP_COOKIE{stuff});
			TODO:
			Add matching for:
				[v] $(HTTP_COOKIE{stuff}|default)
				[v] $(HTTP_COOKIE{stuff}|'default blah')

			Add variable replacement for:
				[v] HTTP_ACCEPT_LANGUAGE
				[v] HTTP_HOST
				[v] HTTP_REFERER
				[v] QUERY_STRING

				HTTP_USER_AGENT
			FIXME: ariadne cookies are serialized by default, which would break HTTP_COOKIE usage in other ESI processors
		*/

		$result = preg_replace_callback('!\$\(([^)|{]*)(\{(([^}]*))\})?(\|([^)]*))?\)!', function($matches) {
					// print_r($matches);

					switch ($matches[1]) {
						case 'HTTP_COOKIE':
							$cookie = ldGetUserCookie($matches[3]);
							$default = preg_replace("/^'(.*?)'$/", "$1", $matches[6]);
							return $cookie ? $cookie : $default;
						break;
						case 'HTTP_HOST':
							$host = ldGetServerVar('HTTP_HOST');
							$default = preg_replace("/^'(.*?)'$/", "$1", $matches[6]);
							return $host ? $host : $default;
						break;
						case 'HTTP_REFERER':
							$referer = ldGetServerVar('HTTP_REFERER');
							$default = preg_replace("/^'(.*?)'$/", "$1", $matches[6]);
							return $referer ? $referer : $default;
						break;
						case 'HTTP_ACCEPT_LANGUAGE':
							$acceptLanguage = ldGetServerVar('HTTP_ACCEPT_LANGUAGE');
							$acceptLanguage = strtolower(str_replace(", ", ",", $acceptLanguage));

							$languages = explode(",", $acceptLanguage);
							if (in_array(strtolower($matches[3]), $languages)) {
								return 1;
							}
							return 0;
						break;
						case 'QUERY_STRING':
							$value = ar_loader::getvar($matches[3], "GET");
							$default = preg_replace("/^'(.*?)'$/", "$1", $matches[6]);
							return isset($value) ? $value : $default;
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

	function esiFetch($url) {
		$scriptName = $_SERVER["SCRIPT_NAME"] ? basename($_SERVER["SCRIPT_NAME"]) : basename($_SERVER["SCRIPT_FILENAME"]);
		if ($scriptName) {
			$scriptName = "/" . $scriptName;
		}

		$scriptName = "/loader.php"; // FIXME: Bij een request buiten Ariadne om kan het een andere scriptname zijn waardoor de include niet werkt.

		$url = ESI::esiExpression( $url );
		if (strstr($url, $scriptName)) {
			// Looks like an Ariadne request, handle it!
			$urlArr = parse_url($url);
			parse_str($urlArr['query'], $_GET);
			// $pathInfo = str_replace($scriptName, '', $urlArr['path']);
			$pathInfo = substr($urlArr['path'], strpos($urlArr['path'], $scriptName)+strlen($scriptName), strlen($urlArr['path']));
			$pathInfo = str_replace("//", "/", $pathInfo);

			ob_start();
				ldProcessRequest($pathInfo);
				$replacement = ob_get_contents();
			ob_end_clean();
			// FIXME: Check of the request went ok or not;

		} else {
			// FIXME: Is it a good idea to do http requests from the server this way?
			$client = ar('http')->client();
			$replacement = $client->get($url);

			if ($client->statusCode != "200") {
				return false;
			}
		}

		return $replacement;
	}

	function esiTry($page) {
		$regExp = '|<esi:try>.*?<esi:attempt>(.*)</esi:attempt>.*?<esi:except>(.*)</esi:except>.*?</esi:try>|Uis';
		$page = preg_replace_callback($regExp, function($matches) {
			$result = ESI::esiProcessAll($matches[1]);
			if ($result === false) {
				$result = ESI::esiProcessAll($matches[2]);
			}
			return $result;
		}, $page);
		return $page;
	}

	function esiChoose($page) {
		$regExp = '|<esi:choose>.*?(<esi:when[^>]*>.*</esi:when>)+.*?<esi:otherwise>(.*)</esi:otherwise>.*?</esi:choose>|is';

		$page = preg_replace_callback($regExp, function($matches) {
			$regExp2 = '|<esi:when[^>]*>(.*?)</esi:when>|is';
			preg_match_all($regExp2, $matches[1], $whens);

			foreach ($whens[0] as $key => $when) {
				$parts = htmlparser::parse($when);
				$test = $parts['children'][0]['attribs']['test'];

				if (ESI::esiEvaluate($test)) {
					return ESI::esiProcessAll($whens[1][$key]);
				}
			}
			return ESI::esiProcessAll($matches[2]);
		}, $page);
		return $page;
	}

	function esiEvaluate($test) {
		global $AR;

		// print_r($test);
		$test = preg_replace('!(\$\([^)]*\))!', '\'$1\'', $test);
		// echo "[2[" . print_r($test, true) . "]]";

		$test = ESI::esiExpression($test);
		// echo "[[" . print_r($test, true) . "]]";

		require_once($AR->dir->install."/lib/modules/mod_pinp.phtml");
		$pinp=new pinp($AR->PINP_Functions, "esilocal->", "\$AR_ESI_this->_");
		$pinp->allowed_functions = array();
		$pinp->language_types['array'] = false;
		$pinp->language_types['object'] = false;

		$compiled=$pinp->compile("<pinp>" . $test . "</pinp>");

		$compiled = preg_replace("/^<\?php(.*)\?>$/s", '$1', $compiled);

		// FIXME: Is eval after the pinp compiler save enough to run?
		$result = eval("return (" . $compiled . ");");

		return $result;
	}

	function esiInclude($page) {
		/* TODO:
			[v] alt
			[v] onerror
		*/
		global $ARCurrent, $AR;

		// parse <esi:include src="view.html">
		$regExp = '|<esi:include.*?>|i';


		preg_match_all($regExp, $page, $matches);

		foreach ($matches[0] as $match) {
			$parts = htmlparser::parse($match);

			$src = $parts['children'][0]['attribs']['src'];
			$alt = $parts['children'][0]['attribs']['alt'];
			$onerror = $parts['children'][0]['attribs']['onerror'];

			$replacement = ESI::esiFetch($src);
			if ($replacement == false && isset($alt)) {
				$replacement = ESI::esiFetch($alt);
			}
			if (
				$replacement == false &&
				isset($onerror) &&
				$onerror == "continue"
			) {
				$replacement = "";
			}

			if ($replacement !== false) {
				$page = str_replace($match, $replacement, $page);
			} else {
				return false;
			}
		}

		return $page;
	}

	function esiProcessAll($page) {
		$page = ESI::esiMarker($page);
		if ($page === false) {return false;}

		$page = ESI::esiRemove($page);
		if ($page === false) {return false;}

		$page = ESI::esiComment($page);
		if ($page === false) {return false;}

		$page = ESI::esiTry($page);
		if ($page === false) {return false;}

		$page = ESI::esiChoose($page);
		if ($page === false) {return false;}

		$page = ESI::esiInclude($page);
		if ($page === false) {return false;}

		$page = ESI::esiVars($page);
		if ($page === false) {return false;}

		return $page;
	}

	function esiProcess($page) {
		/*
			TODO:
				inline

				[v] choose/when/otherwise
				[v] try/attempt/except
				[v] include
				[v] comment
				[v] remove
				[v] vars
				[v] <!-- esi -->
		*/

		$page = ESI::esiProcessAll($page);
		if ($page === false) {
			ldObjectNotFound("", "esiInclude failed");
		}
		return $page;
	}
}
