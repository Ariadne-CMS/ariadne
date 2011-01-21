<?php
	ar_pinp::allow( 'ar_html' );
	ar_pinp::allow( 'ar_htmlNode' );
	ar_pinp::allow( 'ar_htmlElement' );
	ar_pinp::allow( 'ar_htmlNodes' );

	class ar_html extends ar_xml {

		public static $xhtml = false;
		public static $preserveWhiteSpace = false;
		private static $emptyTags = array(
			'input' => 1, 'br'       => 1, 'hr'      => 1, 'img'  => 1, 'link'  => 1, 'meta' => 1, 'frame' => 1, 
			'base'  => 1, 'basefont' => 1, 'isindex' => 1, 'area' => 1, 'param' => 1, 'col'  => 1, 'embed' => 1
		);

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
		
		public function __set( $name, $value ) {
			ar_html::configure( $name, $value );
		}
		
		public function __get( $name ) {
			if ( isset( ar_html::${$name} ) ) {
				return ar_html::${$name};
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
							$type = '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-' . $type . '.dtd"';
						} else {
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
			return new ar_htmlNode($doctype);
		}
		
		public static function canHaveContent( $name ) {
			return !isset( self::$emptyTags[strtolower($name)] );
		}
		
		public static function tag() {
			$args = func_get_args();
			return call_user_func_array( array( 'ar_html', 'el' ), $args );
		}
		
		public static function element() {
			$args = func_get_args();
			return call_user_func_array( array( 'ar_html', 'el' ), $args );
		}		
	
		public static function el() {
			$args = func_get_args();
			$name = array_shift($args);
			$attributes = array();
			$childNodes = array();
			foreach ($args as $arg) {
				if ( is_array( $arg ) && !is_a( $arg, 'ar_xmlNodes' ) ) {
					$attributes = array_merge($attributes, $arg);
				} else if ($arg instanceof ar_xmlNodes) {
					$childNodes = array_merge($childNodes, (array) $arg);
				} else {
					$childNodes[] = $arg;
				}
			}
			if ( !count( $childNodes ) ) {
				$childNodes = null;
			} else {
				$childNodes = new ar_htmlNodes( $childNodes );
			}
			return new ar_htmlElement($name, $attributes, $childNodes);
		}
			
		public static function nodes() {
			$args  = func_get_args();
			$nodes = call_user_func_array( array( 'ar_htmlNodes', 'mergeArguments' ), $args );
			return new ar_htmlNodes( $nodes );
		}

		public static function form( $fields, $buttons=null, $action='', $method='POST' ) {
			return new ar_html_form( $fields, $buttons, $action, $method );
		}
		
		public static function table( $rows, $header = null, $rowHeader = null, $foot = null ) {
			return new ar_html_table( $rows, $header, $rowHeader, $foot);
		}
		
		public static function menu( $attributes = null, $list = null ) {
			return new ar_html_menu( $attributes, $list );
		}
		
		public static function zen( $string ) {
			return new ar_html_zen( $string );
		}
		
		protected static function parseChildren( $DOMElement ) {
			$result = array();
			foreach ( $DOMElement->childNodes as $child ) {
				if ( $child instanceof DOMCharacterData ) {
					if ( self::$preserveWhiteSpace || trim( $child->data ) ) {
						$result[] = $child->data;
					}
				} else if ( $child instanceof DOMCdataSection ) {
					if ( self::$preserveWhiteSpace || trim( $child->data ) ) {
						$result[] = self::cdata( $child->data );
					}
				} else if ( $child instanceof DOMNode ) {
					$result[] = self::el( $child->tagName, self::parseAttributes( $child ), self::parseChildren( $child ) );
				}
			}
			return self::nodes( $result );
		}

		public static function parse( $html ) {
			$dom = new DOMDocument();
			if ( $dom->loadHTML( $html ) ) {
				$domroot = $dom->documentElement;
				if ( $domroot ) {
					$result = self::parseHead( $dom );
					$result[] = self::el( $domroot->tagName, self::parseAttributes( $domroot ), self::parseChildren( $domroot ) );
					return $result;
				}
			}
			return ar_error::raiseError( 'Incorrect html passed', ar_exceptions::ILLEGAL_ARGUMENT );
		}		
	}

	class ar_htmlNodes extends ar_xmlNodes {
	
		public function toString( $indentWith = null ) {
			$indent = isset($indentWith) ? $indentWith : ( 
				ar_html::$indenting ? ar_html::$indent : ''
			);
			return parent::toString( $indent );
		}

		public function __toString() {
			return $this->toString();
		}
		
		public function getNodeList() {
			$params = func_get_args();
			return call_user_func_array( array( 'ar_html', 'nodes'), $params );
		}
		
	}

	class ar_htmlNode extends ar_xmlNode {
	
	}
	
	class ar_htmlElement extends ar_xmlElement {
	
		public function __toString() {
			return $this->toString();
		}

		public function toString( $indent = '', $current = 0 ) {
			$indent = ar_html::$indenting ? $indent : '';
			$result = "\n" . $indent . '<' . ar_html::name( $this->tagName );
			if ( is_array($this->attributes) ) {
				foreach ( $this->attributes as $name => $value ) {
					$result .= ar_html::attribute($name, $value, $current);
				}
			} else if ( is_string($this->attributes) ) {
				$result .= ltrim(' '.$this->attributes);
			}
			if ( !ar_html::$xhtml || ar_html::canHaveContent( $this->tagName ) ) {
				$result .= '>';
				if ( ar_html::canHaveContent( $this->tagName ) ) {
					if ( isset($this->childNodes) && count($this->childNodes) ) {
						$result .= $this->childNodes->toString( ar_html::$indent . $indent );
						if ( substr($result, -1) == ">") {
							$result .= "\n" . $indent;
						}
					}
					$result .= '</' . ar_html::name( $this->tagName ) . '>';
				}
			} else {
				$result .= ' />';
			}
			return $result;
		}
		
		public function getNodeList() {
			$params = func_get_args();
			return call_user_func_array( array( 'ar_html', 'nodes'), $params );
		}
	}
?>