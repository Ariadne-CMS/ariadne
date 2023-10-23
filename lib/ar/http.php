<?php

	ar_pinp::allow( 'ar_http');
	ar_pinp::allow( 'ar_httpClientStream' );

	/*
	 * prevent mess detector from warning for the private static fields
	 * @SuppressWarnings(PHPMD.UnusedPrivateField)
	 */
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
				case 'tainting' :
					return self::$tainting;
				break;
			}
		}

		public function __set( $var, $value ) {
			if ($var=='tainting') {
				self::$tainting = (bool) $value;
			}
		}

		public static function getvar( $name = null, $method = null) {
			/*
				The full list of field-name characters that PHP converts to _ (underscore) is the following (not just dot):

				chr(32) ( ) (space)
				chr(46) (.) (dot)
				chr(91) ([) (open square bracket)
				chr(128) - chr(159) (various)
				PHP irreversibly modifies field names containing these characters in an attempt to maintain compatibility with the deprecated register_globals feature.
			*/
			if (isset($name) ) {
				$name = preg_replace("/[ \.\[\x80-\x9f]/", "_", $name);
			}

			switch($method) {
				case 'GET' :
					$result = isset($name) ? ( $_GET[$name] ?? null ) : $_GET;
				break;
				case 'POST' :
					$result = isset($name) ? ( $_POST[$name] ?? null ) : $_POST;
				break;
				case 'COOKIE' :
					$result = isset($name) ? ( $_COOKIE[$name] ?? null ) : $_COOKIE;
				break;
				case 'SERVER' :
					$result = isset($name) ? ( $_SERVER[$name] ?? null ) : $_SERVER;
				break;
				default :
					$result = !isset($name) ? $_REQUEST :
						( $_POST[$name] ?? ( $_GET[$name] ?? null ) );
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

		public function get( $url, $request = null, $options = array() );

		public function post( $url, $request = null, $options = array() );

		public function put( $url, $request = null, $options = array() );

		public function delete( $url, $request = null, $options = array() );

		public function send( $type, $url, $request = null, $options = array() );

		public function headers( $headers );

	}

	class ar_httpClientStream extends arBase implements ar_httpClient {

		private $options = array();

		public $responseHeaders = null;

		/* FIXME: function not used, is it still relevant?
		private function parseRequestURL( $url ) {
			$request = explode( '?', (string) $url );
			if ( isset($request[1]) ) {
				return $request[1];
			} else {
				return null;
			}
		}
		*/

		private function compileRequest( array $request ) {
			$result = "";
			foreach ( $request as $key => $value ) {
				if ( !is_integer( $key ) ) {
					$result .= urlencode($key) . "=" . urlencode($value) . "&";
				}
			}
			return substr( $result, 0, -1);
		}

		private function mergeOptions( ) {
			$args = func_get_args();
			array_unshift( $args, $this->options );
			return call_user_func_array( 'array_merge', $args );
		}

		public function send( $type, $url, $request = null, $options = array() ) {
			if ( is_array( $request ) ) {
				$request = $this->compileRequest( $request );
			}
			$options = $this->mergeOptions( array(
				'method' => $type,
				'content' => $request
			), $options );

			if ( $options['method'] == 'GET' && $options['content'] ) {
				if ( strpos( $url, '?' ) === false ) {
					$url = $url . '?' . $options['content'];
				} else {
					$url = $url . '&' . $options['content'];
				}
				$options['content'] = '';
			}
			$context = stream_context_create( array( 'http' => $options ) );
			$result = @file_get_contents( (string) $url, false, $context );

			$this->responseHeaders = $http_response_header; //magic php variable set by file_get_contents.
			if (is_array($this->responseHeaders) && isset($this->responseHeaders[0])) {
				$statusLine = explode(" ", $this->responseHeaders[0]);
				$this->statusCode = $statusLine[1];
			}

			$this->requestHeaders = $options['header'];
			return $result;
		}

		public function __construct( $options = array() ) {
			$this->options = $options;
		}

		public function get( $url, $request = null, $options = array() ) {
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
