<?php
	class ar_http extends arBase {
		private static $_GET, $_POST, $_REQUEST;  //needed to make __get() work
		
		public function __get($var) {
			switch ($var) {
				case '_GET' : return $this->getvar(null, 'GET');
				break;
				case '_POST' : return $this->getvar(null, 'POST');
				break;
				case '_REQUEST' : return $this->getvar();
				break;
			}
		}

		public static function getvar($name=null, $method=null) {
			if (!isset($name)) {
				switch($method) {
					case 'GET' : return $_GET;
					break;
					case 'POST' : return $_POST;
					break;
					default : return $_REQUEST;
					break;
				}
			} else if ($method!='GET' && isset($_POST[$name])) {
				return $_POST[$name];
			} else if ($method!='POST' && isset($_GET[$name])) {
				return $_GET[$name];
			} else {
				return null;
			}
		}

		public static function request($method=null, $url=null, $postdata=null, $port=null) {
			return new ar_httpRequest($method, $url, $postdata, $port);
		}
	}
	
	class ar_httpRequest extends arBase {
		private $resultContent = '';
		private $resultHeaders = array();
		private $requestHeaders = array();
		private $requestData = '';
		private $requestString = '';
		private $connection = null;
		
		public function __construct($method=null, $url=null, $data=null, $port=null) {
			if ($method && $url) {
				$this->send($method, $url, $data, $port);
			}
		}
		
		public function __toString() {
			return $this->resultContent;
		}
		
		public function getHeaders() {
			return $this->resultHeaders;
		}
		
		public function addHeader($header) {
			$this->requestHeaders[] = $header;
		}
		
		public function get($url, $port=null) {
			return $this->send('GET', $url, null, $port);
		}
		
		public function post($url, $postdata=null, $port=null) {
			return $this->send('POST', $url, $postdata, $port);
		}
		
		private function assembleRequestData($data) {
			if (isset($data) && is_array($data)) { 
				foreach($data as $key=>$val) { 
					if(!is_integer($key)) {
						$this->requestData .= "$key=".urlencode($val)."&"; 
					}
				} 
			} else if (isset($data) && is_string($data)) {
				$this->requestData = $data;
			}
		}

		private function sendRequest($method, $host, $uri) {
			if( strtoupper($method) == "GET" ) { 
				if ($this->requestData) {
					$uri .= "?" . $this->requestData; 
				}
				$this->requestString .= "GET $uri HTTP/1.0\r\n"; 
			} else if( strtoupper($method) == "POST" ) { 
				$this->requestString .= "POST $uri HTTP/1.0\r\n"; 
			} else {
				$this->requestString .= "$method $uri HTTP/1.0\r\n";
			}

			$this->requestString .= 
				"Host: $host\r\n" .
				"Accept: */*\r\n" .
				"Accept: image/gif\r\n" .
				"Accept: image/x-xbitmap\r\n" .
				"Accept: image/jpeg\r\n"; 

			$this->sendRequestHeaders();
			
			if( strtoupper($method) == "POST" ) { 
				$strlength = strlen( $this->requestData); 
				$this->requestString .=
					"Content-type: application/x-www-form-urlencoded\r\n" . 
					"Content-length: ".$strlength."\r\n\r\n" .
					$this->requestData."\r\n";
			} 
			
			$this->requestString .= "\r\n";
			fwrite($this->connection, $this->requestString, strlen($this->requestString));
		}

		private function sendRequestHeaders() {
			if (count($this->requestHeaders)) {
				$this->requestString .= join("\r\n", $this->requestHeaders)."\r\n";
			}
		}
		private function parseLocation($line, $url) {
			if (preg_match("/^Location: (.+?)\n/is",$line,$matches) ) { 
				//redirects are sometimes relative 
				$newurl = $matches[1]; 
				if (!preg_match("/http:\/\//i", $newurl, $matches) ) { 
					$url .= $newurl; 
				} else { 
					$url = $newurl; 
				} 
				//extra \r's get picked up sometimes 
				//i think only with relative redirects 
				//this is a quick fix. 
				$url = preg_replace("/\r/s","",$url); 
				return $url;
			} else {
				return false;
			}
		}
		public function send($method='GET', $uri, $data=null, $port=null) {
			$urlComponents = parse_url($uri);
			if ($port) {
				$urlComponents['port'] = $port;
			}
			if (!$urlComponents['port']) {
				$urlComponents['port'] = 80;
			}
			if ($urlComponents['scheme']!='http') {
				return ar::error('Bad Request', 400);
			} else {
				
				$maxtries = 5;
				$tries = 0;
				$redirecting = true;

				$this->assembleRequestData($data);
				while ($redirecting && $tries < $maxtries) {
					$tries++; 

					$this->connection = fsockopen( $urlComponents['host'], $urlComponents['port'], $errno, $errstr, 120); 
					if( $this->connection ) { 
						$this->sendRequest($method, $urlComponents['host'], $urlComponents['path']);
						$output = ""; 

						$headerStart = false; 
						$headerEnd = false; 
						$redirecting = false; 
						$this->resultContent = '';
						$this->resultHeaders = array();
						
						while (!feof($this->connection)) { 
							$currentLine = fgets ($this->connection, 1024); 
							if ($headerEnd && $redirecting) { 
								break; 
							} else if ($headerEnd && !$redirecting) { 
								$this->resultContent .= $currentLine; 
							} else if ( ereg("^HTTP", $currentLine) ) { 
								$headerStart = true; 
								$this->resultHeaders[] = $currentLine;
							} else if ( $headerStart && preg_match('/^[\n\r\t ]*$/', $currentLine) ) { 
								$headerEnd = true; 
							} else { 
								if ($newurl = $this->parseLocation($currentLine, $url)) {
									$url = $newurl;
									$redirecting = true; 
								} else {
									$this->resultHeaders[] =$currentLine;
								}
							} 
						} 
					} else {
						return ar::error($errstr, $errno);
					}
					@fclose($this->connection); 
				}
				
				return $this;
			}
		}
	}
?>