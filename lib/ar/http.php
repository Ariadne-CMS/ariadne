<?php
	class ar_http extends ar_base {
		private static $_GET, $_POST, $_REQUEST;  //needed to make __get() work
		
		public static function getvar($name=null, $method=null) {
			if (!isset($name)) {
				switch($method) {
					case 'GET' : return $_GET;
					break;
					case 'POST' : return $_POST;
					break;
					default : return $_REQUEST;
					break;
				}
			} else if ($method!='GET' && isset($_POST[$name])) {
				return $_POST[$name];
			} else if ($method!='POST' && isset($_GET[$name])) {
				return $_GET[$name];
			} else {
				return null;
			}
		}
		
		public function __get($var) {
			switch ($var) {
				case '_GET' : return $this->getvar(null, 'GET');
				break;
				case '_POST' : return $this->getvar(null, 'POST');
				break;
				case '_REQUEST' : return $this->getvar();
				break;
			}
		}
		
	}
?>