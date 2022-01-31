<?php
	if ($this->CheckConfig()) {
		$query = "";
		if( count($_GET) ) {
			$query = "?".http_build_query($_GET);
		}
		var_dump($arRequestedTemplate);
		var_dump($query);
		ldRedirect($this->make_local_url().$arRequestedTemplate.$query);
	}
?>
