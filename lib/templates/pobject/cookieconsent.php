<?php
	$cookieconsent = true;
	$noconsent = $this->getvar('noconsent');
	if (isset($noconsent)) {
		$cookieconsent = false;
	}
	ldSetUserCookie($cookieconsent, "ARCookieConsent", time()+60*60*24*365); // Cookie consent, valid for 1 year.
?>
