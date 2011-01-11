<?php

	ar_pinp::allow( 'ar_url');
	ar_pinp::allow( 'ar_urlQuery' );

	class ar_url extends arBase implements arKeyValueStoreInterface {
	
		private $components, $query;
		
		public function __construct( $url ) {
			$this->components = parse_url( $url );
			// FIXME: make option to skip parsing of the query part
			$this->query = new ar_urlQuery( $this->components['query'] );
		}
		
		public function __get($var) {
			if ($var=='password') {
				$var = 'pass';
			}
			if ($var=='query') {
				return $this->query;
			} else if ( isset( $this->components[$var] ) ) {
				return $this->components[$var];
			} else {
				return null;
			}
		}
		
		public function __set($var, $value) {
			switch($var) {
				case 'query' :
					if (is_string($value)) {
						$this->query = new ar_urlQuery($query);
					} else if ($value instanceof ar_urlQuery) {
						$this->query = $value;
					} else if (is_object($value) && method_exists($value, '__toString') ) {
						$this->query = new ar_urlQuery($value);
					}
				break;
				case 'path' :
					$this->components[$var] = $value;
				break;
				case 'password' :
					$var = 'pass';
					$this->components[$var] = $value;
				break;
				case 'scheme':
				case 'host' :
				case 'port' :
				case 'user' :
				case 'pass' :
				case 'fragment' :
					$this->components[$var] = $value;
				break;
			}
		}

		public function __toString() {
			$url = '';
			if ($this->components['host']) {
				if ($this->components['scheme']) {
					$url .= $this->components['scheme'].'://';
				}
				if ($this->components['user']) {
					$url .= $this->components['user'];
					if ($this->components['pass']) {
						$url .= ':'.$this->components['pass'];
					}
					$url .= '@';
				}
				$url .= $this->components['host'];
				if ($this->components['port']) {
					$url .= ':'.$this->components['port'];
				}
				if ($this->components['path']) {
					if (substr($this->components['path'], 0, 1)!=='/') {
						$url.= '/';
					}
				}
			}
			$url .= $this->components['path'];
			$query = ''.$this->query;
			if ($query) {
				$url .= '?' . $query ;
			}
			if ($this->components['fragment']) {
				$url .= '#' . $this->components['fragment'];
			}
			return $url;
		}
		
		public function getvar( $name ) {
			return $this->query->$name;
		}
		
		public function putvar( $name, $value ) {
			$this->query->{$name} = $value;
		}
		
	}
	
	class ar_urlQuery extends arBase implements arKeyValueStoreInterface {
		
		private $arguments = array();
		
		public function __construct( $query ) {
			if ($query) {
				// FIXME: parse_str cannot handle all types of query string
				// ?val&1+2=3  =>  val=&1_2=3
				parse_str( $query, $this->arguments );
				if (ar_http::$tainting) {
					ar::taint($this->arguments);
				}
			}
		}
		
		public function __get( $var ) {
			return $this->getvar( $name );
		}
		
		public function __set( $var, $value ) {
			return $this->putvar( $name, $value );
		}
		
		public function getvar( $name ) {
			if (isset($this->arguments[$var]) ) {
				return $this->arguments[$var];
			} else {
				return null;
			}
		}
		
		public function putvar( $name, $value ) {
			$this->arguments[$var] = $value;
		}

		public function __toString() {
			$arguments = $this->arguments;
			ar::untaint( $arguments, FILTER_UNSAFE_RAW);
			// FIXME: http_build_query cannot build all query strings, see above about parse_str
			$result = http_build_query( (array) $arguments );
			$result = str_replace( '%7E', '~', $result ); // incorrectly encoded, obviates need for oauth_encode_url
			return $result;
		}
		
		public function import( $values ) {
			if ( is_string( $values ) ) {
				parse_str( $values, $result );
				$values = $result;
			}
			if ( is_array( $values) ) {
				$this->arguments = $values + $this->arguments;
			}
		}
		
		
	}
	
?>