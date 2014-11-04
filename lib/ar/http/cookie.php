<?php

	ar_pinp::allow('ar_http_cookie');

	class ar_http_cookie extends arBase {

		public static function get( $name = "ARUserCookie" ) {
			return new ar_http_cookieStore( $_COOKIE[$name], array( 'name' => $name ) );
		}

	}

	class ar_http_cookieStore extends arBase implements arKeyValueStoreInterface {

		protected $values = array();
		protected $name = 'ARUserCookie';
		protected $configuration = array(
			'expire' => null,
			'path'   => '/',
			'domain' => '',
			'secure' => false
		);

		public function __construct( $name = 'ARUserCookie', $cookie = null, $configuration = array() ) {
			if ( $name == 'ARCookie' ) {
				// prevent access to Ariadne's authentication cookie
				$name = 'ARUserCookie';
			}
			$this->name = $name;
			$this->values = unserialize( $cookie );
			$this->configure( $configuration );
		}

		public function __set( $name, $value ) {
			$this->putvar( $name, $value );
		}

		public function __get( $name ) {
			$this->getvar( $name );
		}

		public function putvar( $name, $value ) {
			$this->values[ $name ] = $value;
		}

		public function getvar( $name ) {
			return $this->value[ $name ];
		}

		public function configure( $name, $value = null ) {
			if ( is_array( $name ) ) {
				$this->configuration = $name + $this->configuration;
			} else {
				$this->configuration[$name] = $value;
			}
		}

		public function save( $name = null ) {
			if ( $name == 'ARCookie' ) {
				// prevent access to Ariadne's authentication cookie
				$name = 'ARUserCookie';
			}
			$this->name = $name;
			return setcookie( $this->name, serialize( $this->value ), $this->configuration['expire'],
				$this->configuration['path'], $this->configuration['domain'], $this->configuration['secure'] );
		}

	}
