<?php

/**
 * lessphp v0.3.8
 * http://leafo.net/lessphp
 *
 * LESS css compiler, adapted from http://lesscss.org
 *
 * Copyright 2012, Leaf Corcoran <leafot@gmail.com>
 * Licensed under MIT or GPLv3, see LICENSE
 */

ar_pinp::allow('ar_beta_lessc');

class ar_beta_lessc extends arBase {
	public static function compile($string) {
		$client = new lessc;
		return $client->compile($string);
	}
}

?>
