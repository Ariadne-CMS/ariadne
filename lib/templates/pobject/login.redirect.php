<?php
	if ($this->CheckConfig()) {
		$query = "";
		if( count($_GET) ) {
			$query = "?".http_build_query($_GET);
		}
		ldRedirect($this->make_local_url().$arRequestedTemplate.$query);
	}
?>
