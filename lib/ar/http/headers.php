<?php
	ar_pinp::allow('ar_http_headers', array(
		'add', 'sent', 'cache', 'disableCache', 'content', 'redirect', 'getStatusMessage'
	));
		
	class ar_http_headers extends arBase {

		public static $headers;
	
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
			ldHeader( $header );
			self::$headers[] = $header;
		}

		public function sent() {
			return Headers_sent();
		}
		
		public function cache($expires=0, $modified=0) {
			return ldSetClientCache(true, $expires, $modified);
		}
		
		public function disableCache() {
			return ldSetClientCache(false);
		}
		
		public function content($mimetype, $size=0) {
			return ldSetContent($mimetype, $size);
		}
		
		public function redirect($URI, $statusCode=0) {
			if ($statusCode && is_numeric($statusCode)) {
				self::add('HTTP/1.1 '.$statusCode.' '.self::getStatusMessage($statusCode));
			}
			self::add('Location: '.$URI);
		}
		
		public function getStatusMessage($statusCode) {
			return self::$statusCodes[$statusCode];
		}
	}
?>