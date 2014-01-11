<?php

	
	interface ar_core_sessionInterface {
		public function __construct($options = null);
		public function parse( $pathInfo );
		public function start( $id = 0 );
	}
	
	class ar_core_session implements ar_core_sessionInterface {
		private $options = array();
		private $modSession = null;
		
		public function __construct($options = null) {
			require_once($options['code'].'modules/mod_session.phtml');
			$this->options = $options;
		}
		
		public function start( $id = 0 ) {
			$this->modSession = new session( $options, $id );
			return $this->modSession->id;
		}
		
		public function __get($name) {
			return $this->modSession->{$name};
		}
		
		public function __call($name, $params) {
			return call_user_func_array( array($this->modSession, $name), $params );
		}

	}
?>