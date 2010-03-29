<?php
	ar_pinp::allow( 'ar_xml' );

	class ar_xml extends arBase {

		private static $indenting = false;
	
		public static $indent = "\t";
		
		public static function configure( $option, $value ) {
			switch ( $option ) {
				case 'indent' :
					if ( is_bool( $value ) ) {
						self::$indenting = (bool) $value;
					} else if ( is_string( $value ) ) {
						self::$indenting = true;
						self::$indent = $value;
					} else if (!$value) {
						self::$indenting = false;
					}
				break;
			}			
		}

		public static function preamble( $version = '1.0', $encoding = 'UTF-8', $standalone = null ) {
			if ( isset($standalone) ) {
				if ( $standalone === 'false' ) {
					$standalone = 'no';
				} else if ( $standalone === 'true' ) {
					$standalone = 'yes';
				}
				$standalone = $this->attribute( 'standalone', $standalone );
			} else {
				$standalone = '';
			}
			return '<?xml version="' . $version . '" encoding="' . $encoding . '"' . $standalone . ' ?>';
		}

		public static function name( $name ) {
			return preg_replace( '/[^a-z0-9:]*/', '', strtolower( $name ) );
		}

		public static function value( $value ) {
			if ( is_array( $value ) ) {
				$content = '';
				foreach( $value as $subvalue ) {
					$content .= ' ' . self::value( $subvalue );
				}
				$content = substr( $content, 1 );
			} else if ( is_bool( $value ) ) {
				$content = $value ? 'true' : 'false';
			} else {
				$content = htmlspecialchars( $value );
			}
			return $content;
		}
		
		public static function attribute( $name, $value ) {
			if ( is_numeric( $name ) ) {					
				return ' ' . self::name( $value );
			} else {
				return ' ' . self::name( $name ) . '="' . self::value( $value ) . '"';
			}
		}
		
		public static function attributes( $attributes ) {
			$content = '';
			if ( is_array( $attributes ) ) {
				foreach( $attributes as $key => $value ) {
					$content .= self::attribute( $key, $value );
				}
			}
			return $content;
		}

		public static function tag() {
			$args = func_get_args();
			$name = $args[0];
			if ( isset($args[1]) ) {
				if ( is_array( $args[1] ) && !is_a( $args[1], 'xmlNodes' ) ) { //attributes
					$attributes = $args[1];
					if ( isset($args[2]) ) {
						$content = $args[2];
					}
				} else { //args[1] is the content
					$content = $args[1];
					if ( isset($args[2]) ) {
						$attributes = $args[2];
					}
				}
			}
			$name = self::name( $name );
			if ( ( isset($content) && $content!=='' ) ) {
				return '<' . $name . self::attributes( $attributes ) . '>' . self::indent( $content ) . '</' . $name . '>';
			} else {
				return '<' . $name . self::attributes( $attributes ) . ' />';
			}
		}
		
		protected static function indent( $content ) {
			if ( self::$indenting && strpos( $content, '<' ) !== false ) {
				return "\n" . preg_replace( '/^(\s*)</m', self::$indent . '$1<', $content ) . "\n"; 
//				return "\n" . preg_replace( "|<([^/])|", "\t<$1", $content );
			} else {
				return $content;
			}
		}
		
		public static function nodes() {
			$args  = func_get_args();
			$nodes = call_user_func_array( array( 'ar_xmlNodes', 'mergeArguments' ), $args );
			return new ar_xmlNodes( $nodes );
		}
	}

	class ar_xmlNodes extends ArrayObject {

		public static function mergeArguments(){
			$args  = func_get_args();
			$nodes = array();
			foreach ( $args as $input ) {
				if ( is_array( $input ) || is_a( $input, 'ar_xmlNodes' ) ) {
					$nodes = array_merge( $nodes, (array) $input );
				} else {
					$nodes[] = $input;
				}
			}
			return $nodes;
		}

		public function __construct() {
			$args  = func_get_args();
			$nodes = call_user_func_array( array( 'ar_xmlNodes', 'mergeArguments' ), $args );
			parent::__construct($nodes);
		}

		public function __toString() {
			return join( "\n", (array) $this );
		}
	}
	
?>