<?php
	global $ariadne;
	require_once($ariadne."/modules/mod_html2text/html2text.inc");

	class pinp_html2text  {
		function _convert($aHtmlText, $aMaxColumns) {
			$html2text =  new Html2Text($aHtmlText, $aMaxColumns);
			return $html2text->convert();
		}
	}
