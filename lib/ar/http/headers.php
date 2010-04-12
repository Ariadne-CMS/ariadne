<?php
	ar_pinp::allow('ar_http_headers', array(
		'add', 'sent', 'cache', 'disableCache', 'content', 'redirect', 'getStatusMessage'
	));
		
	class ar_http_headers extends arBase {

		public static $headers;
		public static $enabled = true;
	
		private $statusCodes = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authorative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Switch Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			425 => 'Unordered Collection',
			426 => 'Upgrade Required',
			449 => 'Retry With',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended'
		);
			
		public static function header( $header ) {
			if ( headers_sent() ) {
				return new ar_error('PHP has already sent the headers. This error can be caused by trailing white space or newlines in the configuration files.', ar_exceptions_configError::HEADERS_SENT);
			}
			if ( is_array($header) ) {
				$header = implode( '\n', $header );
			}
			if ( self::$enabled ) {
				self::$headers[] = $header;
				return ldHeader( $header );
			} else {
				return false;
			}
		}

		public function sent() {
			return Headers_sent();
		}
		
		public function cache( $expires = null, $modified = null ) {
			if ( self::$enabled ) {
				return ldSetClientCache(true, $expires, $modified);
			} else {
				return false;
			}
		}
		
		public function disableCache() {
			if ( self::$enabled ) {
				return ldSetClientCache(false);
			} else {
				return false;
			}
		}
		
		public function content($mimetype, $size=0) {
			if ( self::$enabled ) {
				return ldSetContent($mimetype, $size);
			} else {
				return false;
			}
		}
		
		public function redirect($URI, $statusCode=0) {
			if ($statusCode && is_numeric($statusCode)) {
				self::header('HTTP/1.1 '.$statusCode.' '.self::getStatusMessage($statusCode));
			}
			return self::header('Location: '.$URI);
		}
		
		public function getStatusMessage($statusCode) {
			return self::$statusCodes[$statusCode];
		}
		
		public function setStatusMessage($statusCode, $statusMessage) {
			self::$statusCodes[$statusCode] = $statusMessage;
		}
	}
?>