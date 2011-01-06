<?php
	require_once(dirname(__FILE__).'/xml.php');
	

	ar_pinp::allow( 'ar_http');

	ar_pinp::allow( 'ar_httpClientStream' );

	class ar_http extends arBase {
	
		private static $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE;  //needed to make __get() work
		public static $tainting = true;
		
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
				case '_SERVER' :
					return $this->getvar( null, 'SERVER');
				break;
				case '_COOKIE' :
					return $this->getvar( null, 'COOKIE');
				break;
			}
		}

		public static function getvar( $name = null, $method = null) {
			$result = null;
			switch($method) {
				case 'GET' : 
					$result = isset($name) ? $_GET[$name] : $_GET;
				break;
				case 'POST' : 
					$result = isset($name) ? $_POST[$name] : $_POST;
				break;
				case 'COOKIE' :
					$result = isset($name) ? $_COOKIE[$name] : $_COOKIE;
				break;
				case 'SERVER' :
					$result = isset($name) ? $_SERVER[$name] : $_SERVER;
				break;
				default : 
					$result = !isset($name) ? $_REQUEST : 
						( isset($_POST[$name]) ? $_POST[$name] : $_GET[$name] );
				break;
			}
			if (self::$tainting) {
				ar::taint( $result );
			}
			return $result;
		}

		public static function request( $method = null, $url = null, $postdata = null, $options = array() ) {
			$client = new ar_httpClientStream(); //$method, $url, $postdata, $port);
			return $client->send( $method, $url, $postdata, $options );
		}

		public static function client( $options = array() ) {
			return new ar_httpClientStream( $options );
		}
		
		public static function configure( $option, $value ) {
			switch ( $option ) {
				case 'tainting' :
					self::$tainting = $value;
				break;
			}
		}
		
		public static function header( $header ) {
			return ar_http_headers::header( $header );
		}
		
		public static function get( $url, $request = null, $options = array() ) {
			return self::request( 'GET', $url, $request, $options);
		}
		
		public static function post( $url, $request = null, $options = array() ) {
			return self::request( 'POST', $url, $request, $options);
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
			$request = explode( '?', (string) $url );
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
			$result = @file_get_contents( (string) $url, false, $context );
			$this->responseHeaders = $http_response_header; //magic php variable set by file_get_contents.
			$this->requestHeaders = $options['header'];
			return $result;
		}

		public function __construct( $options = array() ) {
			$this->options = $options;
		}

		public function get( $url, $request = null, $options = array() ) {
			
			if ( !isset($request) ) {
				$request = $this->parseRequestURL($url);
			}
			return $this->send( 'GET', $url, $request, $options );		
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
			$this->options['header'] = $this->options['headers'].$headers;
			return $this;
		}
	}

?>