<?php

	ar_pinp::allow('ar_http_cookie');

	class ar_http_cookie extends arBase {

		public static function get( $name = "ARUserCookie" ) {
			return new ar_http_cookieStore( $name, is_string($name) ? ($_COOKIE[$name] ?? null) : null );
		}

	}

	class ar_http_cookieStore extends arBase {

		public $values = array();
		protected $name = 'ARUserCookie';
		protected $configuration = array(
			'expire' => null,
			'path'   => '/',
			'domain' => '',
			'secure' => false
		);

		public function __construct( $name = 'ARUserCookie', $cookie = null, $configuration = array() ) {
			$this->name = self::safeName($name);
			if ( $name !== $this->name ) {
				// Renaming a protected cookie must also discard its original value.
				$cookie = null;
			}
			if ( is_string($cookie) ) {
				$values = json_decode($cookie, true);
				// This API is a key/value store, not a raw or scalar cookie reader.
				if ( is_array($values) ) {
					$this->values = $values;
				}
			}
			$this->configure( $configuration );
		}

		private static function safeName( $name ) {
			if ( !is_string($name) || $name === '' ) {
				throw new InvalidArgumentException('Cookie name must be a non-empty string');
			}
			foreach ( array('ARSessionCookie', 'ARCurrentSession', 'ARCookie') as $protected ) {
				if ( $name === $protected || str_starts_with($name, $protected.'[') ) {
					return 'ARUserCookie';
				}
			}
			return $name;
		}

		public function __set( $name, $value ) {
			$this->putvar( $name, $value );
		}

		public function __get( $name ) {
			return $this->getvar( $name );
		}

		public function putvar( $name, $value ) {
			if ( !is_array($this->values) ) {
				$this->values = array();
			}
			$this->values[ $name ] = $value;
		}

		public function getvar( $name ) {
			return is_array($this->values) ? ($this->values[ $name ] ?? null) : null;
		}

		public function configure( $name, $value = null ) {
			if ( is_array( $name ) ) {
				$this->configuration = $name + $this->configuration;
			} else {
				$this->configuration[$name] = $value;
			}
		}

		public function save( $name = null ) {
			$name = $name === null ? $this->name : self::safeName($name);
			if ( !is_array($this->values) ) {
				return false;
			}
			$cookie = json_encode($this->values);
			if ( $cookie === false ) {
				return false;
			}
			$result = setcookie( $name, $cookie, $this->configuration['expire'] ?? 0,
				$this->configuration['path'], $this->configuration['domain'], $this->configuration['secure'] );
			if ( $result ) {
				$this->name = $name;
			}
			return $result;
		}

	}
