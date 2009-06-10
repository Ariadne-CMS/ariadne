<?php
	class ar_http_cookie extends arBase {
		public static function set($cookie, $cookiename="ARUserCookie", $expire=null, $path="/", $domain="", $secure=0) {
			return ldSetUserCookie($cookie, $cookiename, $expire, $path, $domain, $secure);
		}
		public static function get($cookiename="ARUserCookie") {
			return ldGetUserCookie($cookiename);
		}
	}
?>