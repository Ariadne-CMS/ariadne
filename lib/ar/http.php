<?php
	ar_pinp::allow( 'ar_http');

	ar_pinp::allow( 'ar_httpClientStream' );

	class ar_http extends arBase {
	
		private static $_GET, $_POST, $_REQUEST;  //needed to make __get() work
		
		public static $headers = array();
		
		public function __get($var) {
			switch ($var) {
				case '_GET' : 
					return $this->getvar( null, 'GET');
				break;
				case '_POST' : 
					return $this->getvar( null, 'POST');
				break;
				case '_REQUEST' : 
					return $this->getvar();
				break;
			}
		}

		public static function getvar( $name = null, $method = null) {
			if (!isset($name)) {
				switch($method) {
					case 'GET' : 
						return $_GET;
					break;
					case 'POST' : 
						return $_POST;
					break;
					default : 
						return $_REQUEST;
					break;
				}
			} else if ( $method != 'GET' && isset($_POST[$name]) ) {
				return $_POST[$name];
			} else if ( $method != 'POST' && isset($_GET[$name]) ) {
				return $_GET[$name];
			} else {
				return null;
			}
		}

		public static function request( $method = null, $url = null, $postdata = null, $options = array() ) {
			$client = new ar_httpClientStream(); //$method, $url, $postdata, $port);
			return $client->send( $method, $url, $postdata, $options );
		}

		public static function client( $options = array() ) {
			return new ar_httpClientStream( $options );
		}
		
		public static function redirect( $uri ) {
			self::header( 'Location: $uri' );
		}

		public static function header( $header ) {
			if ( headers_sent() ) {
				return new ar_error('PHP has already sent the headers. This error can be caused by trailing white space or newlines in the configuration files.', ar_exceptions_configError::HEADERS_SENT);
			}
			if ( is_array($header) ) {
				$header = implode( '\n', $header );
			}
			header( $header );
			self::$headers[] = $header;
		}
	}
	
	interface ar_httpClient {
		public function __construct( $options = array() );

		public function get( $url, $request = null, $options = array() );

		public function post( $url, $request = null, $options = array() );

		public function put( $url, $request = null, $options = array() );

		public function delete( $url, $request = null, $options = array() );

		public function send( $type, $url, $request, $options = array() );
		
		public function headers( $headers );

	}

	class ar_httpClientStream extends arBase implements ar_httpClient {

		private $options = array();

		public $responseHeaders = null;

		private function parseRequestURL( $url ) {
			$request = explode( '?', $url );
			if ( isset($request[1]) ) {
				return $request[1];
			} else {
				return null;
			}
		}

		private function compileRequest( array $request ) {
			$result = "";
			foreach ( $request as $key => $value ) { 
				if ( !is_integer( $key ) ) {
					$result .= urlencode($key)."=".urlencode($val)."&"; 
				}
			} 
			return $result;	
		}

		private function mergeOptions( ) {
			$args = func_get_args();
			array_unshift( $args, $this->options );
			return call_user_func_array( 'array_merge', $args );
		}

		public function send( $type, $url, $request, $options = array() ) {
			if ( is_array( $request ) ) {
				$request = $this->compileRequest( $request );
			}
			$options = $this->mergeOptions( array(
				'method' => $type,
				'content' => $request
			), $options );
			$context = stream_context_create( array( 'http' => $options ) );
			$result = file_get_contents( $url, false, $context );
			$this->responseHeaders = $http_response_header;
			return $result;
		}

		public function __construct( $options = array() ) {
			$this->options = $options;
		}

		public function get( $url, $request = null, $options = array() ) {
			if ( !isset($request) ) {
				$request = $this->parseRequestURL($url);
			}
			return $this->send( 'POST', $url, $request, $options );		
		}

		public function post( $url, $request = null, $options = array() ) {
			return $this->send( 'POST', $url, $request, $options );		
		}

		public function put( $url, $request = null, $options = array() ) {
			return $this->send( 'PUT', $url, $request, $options );		
		}

		public function delete( $url, $request = null, $options = array() ) {
			return $this->send( 'DELETE', $url, $request, $options );		
		}

		public function headers( $headers ) {
			if (is_array($headers)) {
				$headers = join("\r\n", $headers);
			}
			$this->options['headers'] = $this->options['headers'].$headers;
			return $this;
		}
	}

?>