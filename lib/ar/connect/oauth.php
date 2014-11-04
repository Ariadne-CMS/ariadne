<?php
	ar_pinp::allow('ar_connect_oauth');
	ar_pinp::allow('ar_connect_oauthClient');

	class ar_connect_oauth extends arBase {

		public static function getSBS( $method, $uri, $request_parameters = array() ) {
			if ( function_exists('oauth_get_sbs') ) {
				return oauth_get_sbs( $method, $uri, $request_parameters );
			} else {
				return ar_error::raiseError( 'OAuth PECL extension not installed.', ar_exceptions::CONFIGURATION_ERROR );
			}
		}

		public static function client( $consumer_key, $consumer_secret, $signature_method = OAUTH_SIG_METHOD_HMACSHA1, $auth_type = 0 ) {
			return new ar_connect_oauthClient( $consumer_key, $consumer_secret, $signature_method, $auth_type );
		}

	}

	class ar_connect_oauthClient extends arWrapper implements ar_httpClient {

		public function __construct( $consumer_key, $consumer_secret, $signature_method= OAUTH_SIG_METHOD_HMACSHA1, $auth_type = 0 ) {
			if ( !class_exists('OAuth') ) {
				return ar_error::raiseError( 'OAuth PECL extension not installed', ar_exceptions::CONFIGURATION_ERROR );
			}
			$oauth = new OAuth( $consumer_key, $consumer_secret, $signature_method, $auth_type );
			$oauth->setRequestEngine(OAUTH_REQENGINE_STREAMS);
			parent::__construct( $oauth );
		}

		public function send( $type, $url, $request = null, $options = array() ) {
			if ( is_string($options['header']) ) {
				$headers = preg_split( '\r\n', $options['header'] );
			} else {
				$headers = (array) $options['header'];
			}
			switch( $type ) {
				case 'GET' : $type = OAUTH_HTTP_METHOD_GET;
				break;
				case 'POST' : $type = OAUTH_HTTP_METHOD_POST;
				break;
				case 'PUT' : $type = OAUTH_HTTP_METHOD_PUT;
				break;
				case 'DELETE' : $type = OAUTH_HTTP_METHOD_DELETE;
				break;
			}
			try {
				if ( $this->wrapped->fetch( (string) $url, $request, $type, $headers ) ) {
					return $this->wrapped->getLastResponse();
				} else {
					return ar_error::raiseError( 'OAuth fetch failed', ar_exceptions::UNKNOWN_ERROR );
				}
			} catch( Exception $e ) {
				return ar_error::raiseError( $e->getMessage(), $e->getCode() );
			}
		}

		public function get( $url, $request = null, $options = array() ) {
			return $this->send( 'GET', (string) $url, $request, $options );
		}

		public function post( $url, $request = null, $options = array() ) {
			return $this->send( 'POST', (string) $url, $request, $options );
		}

		public function put( $url, $request = null, $options = array() ) {
			return $this->send( 'PUT', (string) $url, $request, $options );
		}

		public function delete( $url, $request = null, $options = array() ) {
			return $this->send( 'DELETE', (string) $url, $request, $options );
		}

		public function headers( $headers ) {
			if (is_array($headers)) {
				$headers = join("\r\n", $headers);
			}
			if ($this->options['header'] && substr( $this->options['header'], -2 )!=="\r\n") {
				$this->options['header'] .= "\r\n";
			}
			$this->options['header'] .= $headers;
			return $this;
		}

	}

?>