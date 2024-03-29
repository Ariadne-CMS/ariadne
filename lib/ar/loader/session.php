<?php
	ar_pinp::allow('ar_loader_session');
	ar_pinp::allow('ar_loader_sessionStore');

	class ar_loader_session extends arBase {

		public static function start() {
			global $ARCurrent;
			ldStartSession(0);
			return new ar_loader_sessionStore( $ARCurrent->session );
		}

		public static function get() {
			global $ARCurrent;
			if ($ARCurrent->session && $ARCurrent->session->id ) {
				return new ar_loader_sessionStore( $ARCurrent->session );
			} else {
				return null;
			}
		}

		public static function kill() {
			global $ARCurrent;
			if ($ARCurrent->session) {
				$ARCurrent->session->kill();
				unset($ARCurrent->session);
			}
		}

	}

	class ar_loader_sessionStore extends arBase implements arKeyValueStoreInterface {

		protected $session = null;

		public function __construct( $session ) {
			$this->session = $session;
		}

		public function getvar( $name ) {
			return $this->session->get( $name );
		}

		public function putvar( $name, $value ) {
			return $this->session->put( $name, $value );
		}

		public function save() {
			return $this->session->save();
		}

		public function suspend() {
			return $this->session->suspend();
		}

		public function kill() {
			return $this->session->kill();
		}

	}
