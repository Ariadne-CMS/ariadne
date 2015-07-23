<?php

	ar_pinp::allow('ar_http_cookie');

	class ar_http_cookie extends arBase {

		public static function get( $name = "ARUserCookie" ) {
			return new ar_http_cookieStore( $name, $_COOKIE[$name] );
		}

	}

	class ar_http_cookieStore extends arBase implements arKeyValueStoreInterface {

		public $values = array();
		protected $name = 'ARUserCookie';
		protected $configuration = array(
			'expire' => null,
			'path'   => '/',
			'domain' => '',
			'secure' => false
		);

		public function __construct( $name = 'ARUserCookie', $cookie = null, $configuration = array() ) {
			if ( $name == 'ARSessionCookie' ) {
				// prevent access to Ariadne's authentication cookie
				$name = 'ARUserCookie';
			}
			$this->name = $name;
			try {
				$this->values = json_decode($cookie);
			} catch(Exception $e) {
			}
			if ( !isset($this->values) && isset($cookie) ) {
				$this->values = $cookie;
			}
			$this->configure( $configuration );
		}

		public function __set( $name, $value ) {
			$this->putvar( $name, $value );
		}

		public function __get( $name ) {
			return $this->getvar( $name );
		}

		public function putvar( $name, $value ) {
			$this->values[ $name ] = $value;
		}

		public function getvar( $name ) {
			return $this->values[ $name ];
		}

		public function configure( $name, $value = null ) {
			if ( is_array( $name ) ) {
				$this->configuration = $name + $this->configuration;
			} else {
				$this->configuration[$name] = $value;
			}
		}

		public function save( $name = null ) {
			if ( $name == 'ARSessionCookie' ) {
				// prevent access to Ariadne's authentication cookie
				$name = 'ARUserCookie';
			}
			$this->name = $name;
			//TODO/FIXME: test save with non-array/non-object value
			return setcookie( $this->name, json_encode( $this->values ), $this->configuration['expire'],
				$this->configuration['path'], $this->configuration['domain'], $this->configuration['secure'] );
		}

	}
