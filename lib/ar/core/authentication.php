<?php
	interface ar_core_authenticationInterface {
		public function getUser( $login );
	}

	class ar_core_authentication {
		private $options = null;
		private $modAuth = null;

		public function __construct( $options, $ariadne ) {
			require_once( '../../modules/mod_auth/'.$options['method'].'.php');
			$this->options = $options;
			$className     = 'mod_auth_'.$options['method'];
			$this->modAuth = new $className( $options );
		}

		public function __call( $name, $params ) {
			return call_user_func_array( array($this->modAuth, $name), $params );
		}
	}
