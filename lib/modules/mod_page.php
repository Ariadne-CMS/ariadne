<?php

include_once($this->store->get_config('code')."modules/mod_url.php");

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

}
	
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

}

?>