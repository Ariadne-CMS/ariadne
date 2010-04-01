<?php
	ar_pinp::allow( 'ar_xml' );

	class ar_xml extends arBase {

		private static $indenting = true;
	
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
			return '<?xml version="' . self::value($version) . '" encoding="' . self::value($encoding) . '"' . $standalone . " ?>\n";
		}

		public static function name( $name ) {
			$name = mb_eregi_replace( '[^-.0-9:a-z_]', '', $name);
			$name = mb_eregi_replace( '^[^:a-z_]*', '', $name);
			return $name;
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
				if ( preg_match( '/^\s*<!\[CDATA\[/', $content ) ) {
					$content = $value;
				} else {
					$content = htmlspecialchars( $value );
				}
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

		public static function cdata( $value ) {
			return '<![CDATA[' . str_replace( ']]>', ']]&gt;', $value ) . ']]>';
		}
		
		public static function tag() {
			$args = func_get_args();
			$name = array_shift($args);
			$attributes = array();
			$content = '';
			foreach ($args as $arg) {
				if ( is_array( $arg ) && !is_a( $arg, 'ar_xmlNodes' ) ) {
					$attributes = array_merge($attributes, $arg);
				} else {
					if ( $content ) {
						$content .= "\n" . $arg;
					} else {
						$content = $arg;
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
		
		public static function indent( $content, $indent=null ) {
			if ( ( isset($indent) || self::$indenting ) && preg_match( '/^(\s*)</', $content) ) {
				if ( !isset($indent) ) {
					$indent = self::$indent;
				}
				return "\n" . preg_replace( '/^(\s*)</m', $indent . '$1<', $content ) . "\n"; 
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