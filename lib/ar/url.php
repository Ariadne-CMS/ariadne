<?php

	ar_pinp::allow( 'ar_url');
	ar_pinp::allow( 'ar_urlQuery' );
	use \arc\url;

	class ar_url extends arBase implements arKeyValueStoreInterface {

		private $url, $query;

		public function __construct( $url ) {
			$query = new \arc\url\PHPQuery();
			$this->url = new \arc\url\Url($url, $query);
			$this->query = new ar_urlQuery($query);
		}

		public function __get($var) {
			if ($var=='password') {
				$var = 'pass';
			}
			if ($var=='query') {
				return $this->query;
			} else if ( isset( $this->url->{$var} ) ) {
				return $this->url->{$var};
			} else {
				return null;
			}
		}

		public function __set($var, $value) {
			switch($var) {
				case 'query' :
					if (is_string($value)) {
						$this->url->query = $value;
						$this->query = new ar_urlQuery($this->url->query);
					} else if ($value instanceof ar_urlQuery) {
						$this->url->query = (string) $value;
					} else if (is_object($value) && method_exists($value, '__toString') ) {
						$this->url->query = (string)$value;
					}
				break;
				case 'password' :
					$var = 'pass';
				case 'path' :
				case 'scheme':
				case 'host' :
				case 'port' :
				case 'user' :
				case 'pass' :
				case 'fragment' :
					return $this->url->{$var} = $value;
				break;
			}
		}

		public function __toString() {
			$query = (array)$this->url->query;
			$url   = clone($this->url);
			ar::untaint($query, FILTER_UNSAFE_RAW);
			$url->query->import($query);
			return (string)$url;
		}

		public static function getvar( $name ) {
			return $this->query->$name;
		}

		public static function putvar( $name, $value ) {
			$this->query->{$name} = $value;
		}

		public function import( $values ) {
			$this->query->import( $values );
		}

	}

	class ar_urlQuery implements arKeyValueStoreInterface, ArrayAccess /*, ArrayAcces, IteratorAggregate, .. */ {
		private $query;

		public function __construct( $query ) {
			if ( $query instanceof \arc\url\Query ){
				$this->query  = $query;
			} else {
				$this->query = new \arc\url\PHPQuery($query);
			}
		}

		public function __setQuery($url, $query) {
			if ( $url->query === $this->query ) {
				$url->query = $this->query = $query->query;
			}
		}

		public function __call( $name, $arguments ) {
			if (($name[0]==='_')) {
				$realName = substr($name, 1);
				if (ar_pinp::isAllowed($this, $realName)) {
					return call_user_func_array(array($this, $realName), $arguments);
				} else {
					trigger_error("Method $realName not found in class ".get_class($this), E_USER_WARNING);
				}
			} else {
				trigger_error("Method $name not found in class ".get_class($this), E_USER_WARNING);
			}
		}

		public function &__get( $name ) {
			return $this->query->{$name};
		}

		public function __set( $name, $value ) {
			$this->query->{$name} = $value;
		}

		public static function getvar( $name ) {
			return $this->query->{$name};
		}

		public static function putvar( $name, $value ) {
			$this->query->{$name} = $value;
		}

		public function __toString() {
			$q = clone $this->query;
			$qa = (array)$q;
			ar::untaint($qa, FILTER_UNSAFE_RAW);
			$q->import($qa);
			return (string)$q;
		}

		public function import( $values ) {
			$this->query->import( $values );
		}

		public function offsetExists($name){
			return isset($this->query->{$name});
		}

		public function &offsetGet($name) {
			return $this->query->{$name};
		}

		public function offsetSet($name, $value) {
			$this->query->{$name} = $value;
		}
		public function offsetUnset($name) {
			unset($this->query->{$name});
		}

	}
