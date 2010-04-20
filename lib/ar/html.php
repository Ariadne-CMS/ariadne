<?php
	ar_pinp::allow( 'ar_html' );
	ar_pinp::allow( 'ar_htmlNode' );
	ar_pinp::allow( 'ar_htmlElement' );
	ar_pinp::allow( 'ar_htmlNodes' );

	class ar_html extends ar_xml {

		public static $xhtml = false;

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
			return new ar_htmlNode($doctype);
		}
		
		public static function canHaveContent( $name ) {
			return !in_array( $name, array( 'input', 'br', 'hr', 'img', 'link', 'meta', 
				'base', 'basefont', 'isindex', 'area', 'param', 'col', 'frame' ) );
		}
		
		public static function tag() {
			$args = func_get_args();
			$name = array_shift($args);
			$attributes = array();
			$childNodes = ar_xml::nodes();
			foreach ($args as $arg) {
				if ( is_array( $arg ) && !is_a( $arg, 'ar_xmlNodes' ) ) {
					$attributes = array_merge($attributes, $arg);
				} else if ($arg instanceof ar_xmlNodes) {
					$childNodes = ar_html::nodes($childNodes, $arg);
				} else {
					$childNodes[] = $arg;
				}
			}
			if ( !count( $childNodes ) ) {
				$childNodes = null;
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
		
	}

	class ar_htmlNodes extends ar_xmlNodes {
	
		public function __toString( $indentWith = null ) {
			$indent = isset($indentWith) ? $indentWith : ( 
				ar_html::$indenting ? ar_html::$indent : ''
			);
			return parent::__toString( $indent );
		}
		
		public function getNodeList() {
			$params = func_get_args();
			return call_user_func_array( array( 'ar_html', 'nodes'), $params );
		}
		
	}

	class ar_htmlNode extends ar_xmlNode {
	
	}
	
	class ar_htmlElement extends ar_xmlElement {
	
		public function __toString( $indent = '', $current = 0 ) {
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
						$result .= $this->childNodes->__toString( ar_html::$indent . $indent );
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