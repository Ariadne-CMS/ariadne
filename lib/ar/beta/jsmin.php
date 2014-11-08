<?php
ar_pinp::allow('ar_beta_jsmin');

class ar_beta_jsmin extends arBase {
	public static function compile($string) {
		return \JSMin\JSMin::minify($string);
	}
}
