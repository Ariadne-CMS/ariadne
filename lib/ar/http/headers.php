<?php
	ar_pinp::allow('ar_http_headers');

	class ar_http_headers extends arBase {

		public static $headers;
		public static $enabled = true;

		private static $statusCodes = array(
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

		public static function header( $header, $replace = true ) {
			global $ARCurrent;
			if ( headers_sent() ) {
				return new ar_error('PHP has already sent the headers. This error can be caused by trailing white space or newlines in the configuration files.', ar_exceptions::HEADERS_SENT);
			}
			if ( is_array($header) ) {
				$header = implode( '\n', $header );
			}
			if ( self::$enabled ) {
				list($key,) = explode(':',$header,2);
				Header($header,$replace);
				if($replace){
					self::$headers[strtolower($key)] = $header;
					if ($ARCurrent) {
						$ARCurrent->ldHeaders[strtolower($key)] = $header;
					}
				} else {
					self::$headers[strtolower($key)] .= $header;
					if ($ARCurrent) {
						$ARCurrent->ldHeaders[strtolower($key)] .= $header;
					}
				}
				return true;
			} else {
				return false;
			}
		}

		public static function sent() {
			return Headers_sent();
		}

		public static function cache( $expires = null, $modified = null, $caching = true ) {
			if ( self::$enabled ) {
				$now = time();
				if ( !isset($modified) ) {
					$modified = $now;
				}
				if ($caching) {
					if ( !isset($expires) ) {
						$expires = $now + 1800;
					}
					$result = self::header("Pragma: cache");
					self::header("Cache-control: public");
				} else {
					if ( !isset($expires) ) {
						$expires = 0;
					}
					$result = self::header("Pragma: no-cache");
					self::header("Cache-control: must-revalidate, max-age=0, private");
				}
				if ( $expires !== false ) {
					self::header("Expires: ".gmdate(DATE_RFC1123, $expires));
				}
				if ( $modified !== false ) {
					self::header("Last-Modified: ".gmdate(DATE_RFC1123, $modified));
				}
				return $result;
			} else {
				return false;
			}
		}

		public static function disableCache() {
			if ( self::$enabled ) {
				return self::cache( 0, time(), false );
			} else {
				return false;
			}
		}

		public static function content($mimetype, $size=0) {
			global $ARCurrent;
			if ( self::$enabled ) {
				$result = self::header("Content-Type: ".$mimetype);
				if ($ARCurrent) {
					$ARCurrent->arContentTypeSent = true;
				}
				if ($size) {
					self::header("Content-Length: ".$size);
				}
				return $result;
			} else {
				return false;
			}
		}

		public static function redirect($URI, $statusCode=0) {
			if ($statusCode && is_numeric($statusCode)) {
				self::header('HTTP/1.1 '.$statusCode.' '.self::getStatusMessage($statusCode));
			}
			return self::header('Location: '.$URI);
		}

		public static function getStatusMessage($statusCode) {
			return self::$statusCodes[$statusCode];
		}

		public static function setStatusMessage($statusCode, $statusMessage) {
			self::$statusCodes[$statusCode] = $statusMessage;
		}
	}
