<?php

ar_pinp::allow('ar_filter');

/*
	'/foo/'
	-> matches a message with root entry 'foo'
	'/foo/,/bar/'
	-> matches a message with both /foo/ and /bar/
	'/foo:bar/'
	-> matches a message with root entry 'foo' with string value 'bar'
	'/foo/bar/'
	-> matches a message with root entry 'foo' with a child entry 'bar'
	'/foo/.../bar/'
	-> matches a message with root entry 'foo' with a descendant entry
	   'bar'
	'/foo:"bar"/'
	-> matches a message with root entry 'foo' whose value contains the
	   string 'bar'
	'!/foo/'
	-> matches a message that has no root entry 'foo'
	'!/.../foo/'
	-> matches a message that has no entry 'foo' at any level
	'/foo:"[a-z][a-z0-9]*"/'
	-> matches a message that has a root entry foo with a value that
	   matches the regular expression / [a-z][a-z0-9]* /i
*/
class ar_filter {

	static public function match( $message, $filter ) {
		if ( !$filter ) {
			return true;
		}
		$quotedRe = '"(?:[^"\\\\]|\\\\.)*"';
		$searchPathRe = "([^\",]|$quotedRe)+";
		$firstSearchPathRe = "/^($searchPathRe)(,|$)/";
		$searchPath = false;
		while ( preg_match( $firstSearchPathRe, $filter, $matches ) ) {
			$searchPath = $matches[1];
			$filter = substr( $filter, strlen( $searchPath ) + 1 );
			if ( !self::matchSearchPath(
				$message,
				trim( $searchPath ) ) )
			{
				return false;
			}
		}
		if ( !$searchPath ) {
			throw new Exception("syntax error in filter ".$filter);
		}
		return true;
	}

	static private function hasValue( $data, $name ) {
		if ( is_array( $data ) || $data instanceof \ArrayObject ) {
			if ( array_key_exists( $name, (array) $data ) ) {
				return true;
			} else {
				return false;
			}
		} else if ( is_object( $data ) && property_exists( $data, $name ) ) {
			return true;
		} else {
			return false;
		}
	}

	static private function getValue( $data, $name ) {
		if ( is_array( $data ) || $data instanceof \ArrayObject ) {
			if ( array_key_exists( $name, (array) $data ) ) {
				return $data[$name];
			} else {
				return null;
			}
		} else if ( is_object( $data ) && property_exists( $data, $name ) ) {
			return $data->{$name};
		} else {
			return null;
		}
	}

	static private function matchSearchPath( $message, $searchPath ) {
		if ( !$searchPath ) {
			return true;
		}
		if ( $searchPath[0] == '!' ) {
			$negate = true;
			$searchPath = substr( $searchPath, 1 );
		} else {
			$negate = false;
		}
		$nameRe = '([^/:\\\\]|[\\\\].)+';
		$firstNameRe = "#^/($nameRe)(:($nameRe)|/|$)#";
		$result = false;
		if ( preg_match( $firstNameRe, $searchPath, $matches ) ) {
			$rawName = $matches[1];
			$rawValue = $matches[4];
			$name = preg_replace( '/\\\\(.)/', '\\1', $rawName );
			$value = preg_replace( '/\\\\(.)/', '\\1', $rawValue);
			if ( $name == '...' ) {
				$searchPath = substr( $searchPath, 4 );
				return self::matchDeepSearchPath( $message, $searchPath, $negate );
			} else {
				if ( self::hasValue( $message, $name ) ) {
					$messageValue = self::getValue( $message, $name );
				} else {
					return $negate;
				}
				if ( $value ) {
					if ( $value[0] == '"' ) { // string regexp match
						$value = substr( $value, 1, -1 );
						$result = preg_match( "/".$value."/", $messageValue );
					} else {
						$result = ( $value == $messageValue );
					}
				} else {
					$searchPath = substr( $searchPath, strlen( $matches[0] ) - 1 );
					if ( $searchPath != '/' ) {
						$result = self::matchSearchPath(
							$messageValue, $searchPath );
					} else {
						$result = true;
					}
				}
			}
			if ( $result ) {
				return !$negate ;
			}
		} else {

		}
		return $negate;
	}

	static private function matchDeepSearchPath( $message, $searchPath, $negate = false ) {
		$result = self::matchSearchpath( $message, $searchPath );
		if ( $result ) {
			return !$negate;
		}
		if ( is_object($message) && !($message instanceof \ArrayObject) ) {
			$message = get_object_vars($message);
		}
		foreach ( (array) $message as $key => $subMessage ) {
			$result = self::matchDeepSearchPath(
				$subMessage, $searchPath );
			if ( $result ) {
				return !$negate;
			}
		}
		return $negate;
	}

}

?>
