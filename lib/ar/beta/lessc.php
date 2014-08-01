<?php

ar_pinp::allow('ar_beta_lessc');

class ar_beta_lessc extends arBase {
	public static function compile($string) {
		$client = new lessc;
		return $client->compile($string);
	}
}

?>
