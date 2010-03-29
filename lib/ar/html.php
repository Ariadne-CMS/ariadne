<?php
	ar_pinp::allow( 'ar_html' );

	class ar_html extends ar_xml {

		private static $xhtml = false;

		public static function configure( $option, $value ) {
			switch ($option) {
				case 'xhtml' : 
					self::$xhtml = (bool)$value;
					break;
				default:
					parent::configure($option, $value);
					break;
			}
		}

		public static function doctype( $type = 'strict', $quirksmode = false ) {
			if ($type) {
				$type = strtolower( $type );
				$version = '';
				switch ( $type ) {
					case 'transitional' :
					case 'frameset' :
						$version = ucfirst( $type );
					case 'strict' :
						if (self::$xhtml) {
							$version = ucfirst( $type );
							$type = '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-' . $type . '.dtd"';						} else {
							$type = '"http://www.w3.org/TR/html4/' . $type . '.dtd"';
						}
					break;
				}
				if ($version) {
					$version = ' ' . $version;
				}
			}
			if (self::$xhtml) {
				$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0' . $version . '//EN"';
			} else {
				$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01' . $version . '//EN"';
			}
			if ( !$quirksmode || self::$xhtml) {
				$doctype .= ' ' . $type;
			}
			$doctype .= ">\n";
			return $doctype;
		}
		
		private static function _mustClose( $name ) {
			return in_array( $name, array( 'script', 'div' ) );
		}
		
		public static function tag() {
			$args = func_get_args();
			$name = $args[0];
			if ( isset($args[1]) ) {
				if ( is_array( $args[1] ) && !is_a( $args[1], 'ar_htmlNodes' ) ) { //attributes
					$attributes = $args[1];
					if (isset($args[2])) {
						$content = $args[2];
					}
				} else { //args[1] is the content
					$content = $args[1];
					if (isset($args[2])) {
						$attributes = $args[2];
					}
				}
			}
			$name = self::name( $name );
			if ( !self::$xhtml || ( isset($content) && $content!=='' ) || self::_mustClose( $name ) ) {
				return '<' . $name . self::attributes( $attributes ) . '>' 
					. self::indent( $content ) . '</' . $name . '>';
			} else {
				return '<' . $name . self::attributes( $attributes ) . '/>';
			}
		}
			
		public static function nodes() {
			$args  = func_get_args();
			$nodes = call_user_func_array( array( 'ar_htmlNodes', 'mergeArguments' ), $args );
			return new ar_htmlNodes( $nodes );
		}

		public static function form( $fields, $buttons=null, $action=null, $method='POST' ) {
			return new ar_html_form( $fields, $buttons, $action, $method );
		}
	}

	class ar_htmlNodes extends ar_xmlNodes {
	}
	
?>