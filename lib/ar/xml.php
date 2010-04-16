<?php
	ar_pinp::allow( 'ar_xml' );

	class ar_xml extends arBase {

		public static $indenting = true;
		private static $comments = true;
		
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
				case 'comments' :
					self::$comments = (bool)$value;
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
			return '<?xml version="' . self::value($version) . '" encoding="' . self::value($encoding) . '"' . $standalone . " ?>";
		}
		
		public static function comment( $comment ) {
			return ( self::$comments ? '<!-- '.self::value( $comment ).' -->' : '' );
		}

		public static function name( $name ) {
			ar::untaint($name, FILTER_UNSAFE_RAW);
			$name = mb_eregi_replace( '[^-.0-9:a-z_]', '', $name);
			$name = mb_eregi_replace( '^[^:a-z_]*', '', $name);
			return $name;
		}

		public static function value( $value ) {
			ar::untaint( $value, FILTER_UNSAFE_RAW );
			if ( is_array( $value ) ) {
				$content = '';
				foreach( $value as $subvalue ) {
					$content = rtrim($content) . ' ' . ltrim( self::value( $subvalue ) );
				}
				$content = trim( $content );
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
			ar::untaint( $value, FILTER_UNSAFE_RAW );
			return '<![CDATA[' . str_replace( ']]>', ']]&gt;', $value ) . ']]>';
		}
		
		public static function tag() {
			$args = func_get_args();
			$name = array_shift($args);
			$attributes = array();
			$content = ar_xml::nodes();
			foreach ($args as $arg) {
				if ( is_array( $arg ) && !is_a( $arg, 'ar_xmlNodes' ) ) {
					$attributes = array_merge($attributes, $arg);
				} else if ($arg instanceof ar_xmlNodes) {
					$content = ar_xml::nodes($content, $arg);
				} else {
					$content[] = $arg;
				}
			}
			if ( !count( $content ) ) {
				$content = null;
			}
			return new ar_xmlTag($name, $attributes, $content);
		}
		
		public static function indent( $content, $indent=null ) {
			if ( ( isset($indent) || self::$indenting ) && preg_match( '/^(\s*)</', $content) ) {
				if ( !isset($indent) ) {
					$indent = self::$indent;
				}
				return "\n" . preg_replace( '/^(\s*)</m', $indent . '$1<', $content ); 
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

		private static function removeEmptyNodes( $var ) {
			return (!trim($var)=='');
		}
		
		public function __toString( $indentWith = null) {		
			$indent = isset($indentWith) ? $indentWith : (ar_xml::$indenting ? ar_xml::$indent : '');
			return join( "\n".$indent, array_filter( (array) $this, array( self, 'removeEmptyNodes' ) ) );
		}
		
		public function setAttributes( array $attributes ) {
			foreach ( $this as $key => $node ) {
				if ($node instanceof ar_xmlTag) {
					$node->setAttributes( $attributes );
				}
			}
		}
		
	}

	class ar_xmlTag extends arBase {
		public $name       = null;
		public $attributes = array();
		public $content    = null;
		
		function __construct($name, $attributes, $content) {
			$this->name       = $name;
			$this->attributes = $attributes;
			$this->content    = $content;
		}
		
		function setAttributes( array $attributes ) {
			$this->attributes = $attributes + $this->attributes;
		}

		function setAttribute( $name, $value, $lpe = null ) {
		
		}
		
		function __toString( $indent = '' ) {
			//var_dump($this);
			$indent = ar_xml::$indenting ? $indent : '';
			$result = "\n" . $indent . '<' . ar_xml::name( $this->name );
			if ( is_array($this->attributes) ) {
				foreach ( $this->attributes as $name => $value ) {
					$result .= ar_xml::attribute($name, $value);
				}
			} else if ( is_string($this->attributes) ) {
				$result .= ltrim(' '.$this->attributes);
			}
			if ( $this->content instanceof ar_xmlNodes && count($this->content) ) {
				$result .= '>';
				foreach ( $this->content as $node ) {
					if ($node instanceof ar_xmlTag) {
						$result .= ($node->__toString(ar_xml::$indent . $indent));
					} else {
						$result .= ar_xml::indent($node, ar_xml::$indent . $indent);
					}
				}
				if ( substr($result, -1) == ">") {
					$result .= "\n" . $indent;
				}
				$result .= '</' . ar_xml::name( $this->name ) . '>';
			} else {
				$result .= ' />';
			}			
			return $result;
		}
	}
	
?>