<?php

include_once($this->store->get_config('code')."modules/mod_url.php");
//include_once($this->store->get_config('code')."modules/mod_debug.php");

class page {

	function getBody($page) {
		return eregi_replace('^.*<BODY[^>]*>', '', eregi_replace('</BODY.*$', '', $page));
	}

	function parse($page, $full=false) {
		if (!$full) {
			$page=page::getBody($page);
		}
		return URL::ARtoRAW($page);
	}

	function isEmpty($page) {
		if (!$full) {
			$page=page::getBody($page);
		}		
		return trim(str_replace('&nbsp;',' ',strip_tags($page, '<img>')))=='';
	}

	function getReferences($page) {
		// Use Perl compatible regex for non-greedy matching
		preg_match_all("/['\"](\{(arSite|arRoot|arBase|arCurrentPage)(\/[a-z][a-z])?}.*?)['\"]/", $page, $matches);
		$refs=preg_replace(array("|{arSite(/[a-z][a-z])?}|","|{arRoot(/[a-z][a-z])?}|","|{arBase(/[a-z][a-z])?}|", "|{arCurrentPage(/[a-z][a-z])?}|"),
			array($this->currentsite(), "", "", $this->path), $matches[1]);
		foreach ($refs as $ref) { 
			if ( substr($ref, -1) != '/' && !$this->exists($ref)) {
				$ref = substr($ref, 0, strrpos($ref, "/")+1);
			}
			$result[] = $ref;
		}
		return $result;
	}

	function cleanHtml($var, $tags="_full", $arEditorSettings='') {
		global $ARCurrent;
		if( !$arEditorSettings ) {
			if (!$ARCurrent->arEditorSettings) {
				$arEditorSettings = $this->call("editor.ini");
			} else {
				$arEditorSettings = $ARCurrent->arEditorSettings;
			}
		}
		if ($arEditorSettings) {
			if ($arEditorSettings["htmlcleaner"]["enabled"]) {
				include_once($this->store->get_config("code")."modules/mod_htmlcleaner.php");
				$config = $arEditorSettings["htmlcleaner"];
				$var = htmlcleaner::cleanup($var, $config);
			}

			if ($arEditorSettings["htmltidy"]["enabled"]) {
				include_once($this->store->get_config("code")."modules/mod_tidy.php");
				$config=$arEditorSettings["htmltidy"];
				$config["temp"]=$this->store->get_config("files")."temp/";
				$config["path"]=$AR->Tidy->path;
				$tidy=new tidy($config);
				$result=$tidy->clean($var);
				if ($result["html"]) {
					$var=$result["html"];
				}
			}
			if ($arEditorSettings[$tags]) {
				$var=strip_tags($var, $arEditorSettings[$tags]);
			}

		}
		//contentlanguage stuff
		$var = url::RAWtoAR($var, $contentLanguage);
	    	return $var;
	}
}

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

	function _getReferences($page) {
		return page::getReferences($page);
	}
	
	function _cleanHtml($var, $tags="_full", $arEditorSettings='') {
		return page::cleanHtml($var, $tags, $arEditorSettings);
	}
}

?>