<?php


//include_once($me->store->get_config('code')."modules/mod_debug.php");

class pinp_page {

	function _getBody($page) {
		return page::getBody($page);
	}

	function _parse($page, $full=false) {
		return page::parse($page, $full);
	}

	function _isEmpty($page, $full=false) {
		return page::isEmpty($page);
	}

	function _clean($page, $settings=false) {
		return page::clean($page, $settings);
	}

	function _compile($page, $language='') {
		return page::compile($page, $language);
	}

	function _getReferences($page) {
		return page::getReferences($page);
	}

	function _stripARNameSpace($page) {
		return page::stripARNameSpace($page);
	}

}

class page {

	private function pregError( $errno ) {
		switch($errno) {
			case PREG_NO_ERROR:
				$result = 'There is no error.';
				break;
			case PREG_INTERNAL_ERROR:
				$result = 'There is an internal error!';
				break;
			case PREG_BACKTRACK_LIMIT_ERROR:
				$result = 'Backtrack limit was exhausted!';
				break;
			case PREG_RECURSION_LIMIT_ERROR:
				$result = 'Recursion limit was exhausted!';
				break;
			case PREG_BAD_UTF8_ERROR:
				$result = 'Bad UTF8 error!';
				break;
			case PREG_BAD_UTF8_OFFSET_ERROR:
				$result = 'Bad UTF8 offset error!';
				break;
			default:
				$result = 'Unknown preg errno '.$errno;
		}
		return $result;
	}

	function getBody($page) {
			$page = preg_replace('|</BODY.*$|is', '', $page);
			$errno = preg_last_error();
			if( $page === null || $errno != PREG_NO_ERROR ){
				debug('preg_replace returned null errno '. $errno .' in ' . __CLASS__ . ':' . __FUNCTION__ . ':' . __LINE__ . '?');
				debug('preg error:'. page::pregError($errno));
				return '';
			}
			$page = preg_replace('/^.*<BODY[^>]*>/is', '', $page);
			$errno = preg_last_error();
			if( $page === null || $errno != PREG_NO_ERROR ){
				debug('preg_replace returned null, errno '. $errno .' in ' . __CLASS__ . ':' . __FUNCTION__ . ':' . __LINE__ . '?');
				debug('preg error:'. page::pregError($errno));
				return '';
			}
			return $page;
	}

	function parse($page, $full=false) {
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];
		include_once($me->store->get_config('code')."modules/mod_url.php");
		if (!$full) {
			$page = page::getBody($page);
		}
		return URL::ARtoRAW($page);
	}

	function isEmpty($page) {
		$page = page::getBody($page);
		return trim(str_replace('&nbsp;',' ',strip_tags($page, '<img><object><embed><iframe>')))=='';
	}

	function clean($page, $settings=false) {
		global $AR;
		global $ARCurrent;
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];

		if( !$settings ) {
			if (!$ARCurrent->arEditorSettings) {
				$settings = $me->call("editor.ini");
			} else {
				$settings = $ARCurrent->arEditorSettings;
			}
		}

		if ($settings["htmlcleaner"]["enabled"] || $settings["htmlcleaner"]===true) {
			include_once($me->store->get_config("code")."modules/mod_htmlcleaner.php");
			$config	= $settings["htmlcleaner"];
			$page 	= htmlcleaner::cleanup($page, $config);
		}

		if ($settings["htmltidy"]["enabled"] || $settings["htmltidy"]===true) {
			include_once($me->store->get_config("code")."modules/mod_tidy.php");
			if ($settings["htmltidy"]===true) {
				$config	= array();
				$config["options"] = $AR->Tidy->options;
			} else {
				$config = $settings["htmltidy"];
			}
			$config["temp"]	= $me->store->get_config("files")."temp/";
			$config["path"]	= $AR->Tidy->path;
			$tidy			= new tidy($config);
			$result			= $tidy->clean($page);
			$page			= $result["html"];
		}

		if ($settings["allow_tags"]) {
			$page			= strip_tags($page, $settings["allow_tags"]);
		}

		return $page;
	}

	function compile($page, $language='') {
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];
		include_once($me->store->get_config('code')."modules/mod_url.php");
		include_once($me->store->get_config('code')."modules/mod_htmlparser.php");
		if (!$language) {
			$language = $me->nls;
		}
		$page = URL::RAWtoAR($page, $language);
		$newpage = $page;
		$nodes = htmlparser::parse($newpage, Array('noTagResolving' => true));
		// FIXME: the isChanged check is paranoia mode on. New code ahead.
		// will only use the new compile method when it is needed (htmlblocks)
		// otherwise just return the $page, so 99.9% of the sites don't walk
		// into bugs. 21-05-2007
		$isChanged = page::compileWorker($nodes);
		if ($isChanged) {
			return htmlparser::compile($nodes);
		} else {
			return $page;
		}
	}

	function compileWorker(&$node) {
		$result = false;
		$contentEditable = "";
		if (isset($node['attribs']['contenteditable'])) {
			$contentEditable = "contenteditable";
		} else if (isset($node['attribs']['contentEditable'])) {
			$contentEditable = "contentEditable";
		}
		if ($contentEditable) {
			$node['attribs']['ar:editable'] = $node['attribs'][$contentEditable];
			unset($node['attribs'][$contentEditable]);
			$result = true;
		}
		if ($node['attribs']['ar:type'] == "template") {
				$path		= $node['attribs']['ar:path'];
				$template	= $node['attribs']['ar:name'];
				$argsarr	= Array();
				if (is_array($node['attribs'])) {
					foreach ($node['attribs'] as $key => $value) {
						if (substr($key, 0, strlen('arargs:')) == 'arargs:') {
							$name = substr($key, strlen('arargs:'));
							$argsarr[$name] = $name."=".$value;
						}
					}
				}
				$args = implode('&', $argsarr);

				$node['children'] = Array();
				$node['children'][] = Array(
					"type" => "text",
					"html" => "{arCall:$path$template?$args}"
				);
				// return from worker function
				return true;
		}
		if (is_array($node['children'])) {
			foreach ($node['children'] as $key => $child) {
				// single | makes the following line always run the compileworker
				// method, while any return true in that method makes $result true
				$result = $result | page::compileWorker($node['children'][$key]);
			}
		}
		return $result;
	}

	function getReferences($page) {
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];
		// Find out all references to other objects
		// (images, links) in this object, so we can
		// warn the user if he tries to delete/rename
		// an object which is still referenced somewhere
		// Use Perl compatible regex for non-greedy matching
		preg_match_all("/['\"](\{(arSite|arRoot|arBase|arCurrentPage)(\/[a-z][a-z])?}.*?)['\"]/", $page, $matches);
		$refs	= preg_replace(
			array(
				"|{arSite(/[a-z][a-z])?}|",
				"|{arRoot(/[a-z][a-z])?}|",
				"|{arBase(/[a-z][a-z])?}|",
				"|{arCurrentPage(/[a-z][a-z])?}|" ),
			array(
				$me->currentsite(),
				"",
				"",
				$me->path),
			$matches[1]);
		foreach ($refs as $ref) {
			if (substr($ref, -1) != '/' && !$me->exists($ref)) {
				// Drop the template name
				$ref	= substr($ref, 0, strrpos($ref, "/")+1);
			}
			$result[]	= $ref;
		}
		return $result;
	}

	function stripARNameSpace($page) {
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];
		include_once($me->store->get_config('code')."modules/mod_htmlcleaner.php");
		$cleanAR = array(
			'rewrite' => array(
				'^(A|IMG|DIV)$' => array(
					'^ar:.*' => false,
					'^arargs:.*' => false,
					'^class' => Array(
						'htmlblock[ ]*uneditable[ ]*' => false
					)
				)
			),
			'delete_emptied' => Array(
				'div', 'a'
			)
		);
		return htmlcleaner::cleanup( $page, $cleanAR );
	}

}

?>