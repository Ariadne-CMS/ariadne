<?php
	ar_pinp::allow( 'ar_xml' );
	ar_pinp::allow( 'ar_xmlElement' );
	ar_pinp::allow( 'ar_xmlNode' );
	ar_pinp::allow( 'ar_xmlNodes' );

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

		public static function value( $value, $current = 0 ) {
			ar::untaint( $value, FILTER_UNSAFE_RAW );
			if ( is_array( $value ) ) {
				$content = '';
				foreach( $value as $subvalue ) {
					$content = rtrim($content) . ' ' . ltrim( self::value( $subvalue, $current ) );
				}
				$content = trim( $content );
			} else if ( is_bool( $value ) ) {
				$content = $value ? 'true' : 'false';
			} else if ( $value instanceof ar_listExpression ) {
				$content = self::value( $value->item( $current ) );
			} else {
				if ( preg_match( '/^\s*<!\[CDATA\[/', $content ) ) {
					$content = $value;
				} else {
					$content = htmlspecialchars( $value );
				}
			}
			return $content;
		}
		
		public static function attribute( $name, $value, $current = 0 ) {
			if ( is_numeric( $name ) ) {					
				return ' ' . self::name( $value );
			} else {
				return ' ' . self::name( $name ) . '="' . self::value( $value, $current ) . '"';
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
			return new ar_xmlElement($name, $attributes, $content);
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

		private $parentNode = null;
	
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
			foreach( $nodes as $key => $node) {
				if (!$node instanceof ar_xmlNode) {
					$nodes[$key] = new ar_xmlNode( $node );
				}
			}
			parent::__construct($nodes);
		}

		public function offsetSet($offset, $value) {
			if (!$value instanceof ar_xmlNode) {
				$value = new ar_xmlNode( $value );
			}
			parent::offsetSet($offset, $value);
		}
		
		private static function removeEmptyNodes( $var ) {
			return (!trim($var)=='');
		}
		
		public function __toString( $indentWith = null) {		
			$result = '';
			$indent = isset($indentWith) ? $indentWith : (ar_xml::$indenting ? ar_xml::$indent : '');
			$list   = array_filter( (array) $this, array( self, 'removeEmptyNodes' ) );
			$count  = 0;
			$total  = count($this);
			foreach ( $list as $node) {
				if ( $node instanceof ar_xmlElement) {
					$result .= ($node->__toString($indentWith, $count, $total));
				} else {
					$result .= ar_xml::indent( (string) $node, $indentWith);
				}
				++$count;
			}
			return $result;
		}
		
		public function setAttributes( array $attributes ) {
			foreach ($attributes as $name => $value) {
				$this->setAttribute( $name, $value );
			}
		}

		private function _runPatterns( $value ) {
			if ($value instanceof ar_listExpression_Pattern) {
				$value = ar::listExpression( (array) $this )->pattern( $value->patterns );
			} else if ( is_array( $value ) ) {
				foreach ($value as $key => $subvalue ) {
					$value[$key] = $this->_runPatterns( $subvalue );
				}
			}
			return $value;
		}
		
		public function setAttribute( $name, $value ) {
			$value = $this->_runPatterns($value);
			foreach ( $this as $key => $node ) {
				if ($node instanceof ar_xmlElement) {
					$node->setAttribute( $name, $value );
				}
			}
		}
		
		public function __get( $name ) {
			switch ( $name ) {
				case 'parentNode' :
					return $this->parentNode;
				break;
				default :
					if (isset($this[0]) && is_object($this[0]) ) {
						$el = $this[0];
						return $el->{$name};
					} else {
						return null;
					}
				break;
			}
		}
		
		public function __set( $name, $value ) {
			switch( $name ) {
				case 'parentNode' :
					$this->setParentNode($value);
				break;
				default :
					if (isset($this[0]) && is_object($this[0]) ) {
						$el = $this[0];
						$el->{$name} = $value;
					} else if ($value instanceof ar_xmlElement) {
						$this[] = $value;
					} else {
						$this[] = ar_xml::tag($name, (string)$value);
					}
				break;
			}
		}
		
		public function __call( $name, $params ) {
			if (($name[0]==='_')) {
				$realName = substr($name, 1);
				if (ar_pinp::isAllowed($this, $realName)) {
					return call_user_func_array(array($this, $realName), $params);
				} else {
					trigger_error("Method $realName not found in class ".get_class($this), E_USER_ERROR);
				}
			} else if (isset($this[0]) && is_object($this[0]) ) {
				$el = $this[0];
				return call_user_func_array( array( $el, $name ), $params );
			} else {
				return null;
			}
		}
		
		function getElementsByTagName( $name ) {
			$nodeList = ar_xml::nodes();
			foreach ($this as $node) {
				if ( $node instanceof ar_xmlElement ) {				
					if ( $node->tagName == $name) {
						$nodeList[] = $node;
					}
					$nodeList = ar_xml::nodes( $nodeList, $node->getElementsByTagName( $name ) );
				}
			}
			return $nodeList;			
		}
		
		function setParentNode( $el ) {
			$this->parentNode = $el;
			foreach ($this as $node) {
				if ($node instanceof ar_xmlElement) {
					$node->parentNode = $el;
				}
			}
		}
		
		function getPreviousSibling( $el ) {
			$pos = $this->getPosition( $el );
			if ( $pos > 0 ) {
				return $this[ $pos - 1 ];
			} else {
				return null;
			}
		}
		
		function getNextSibling( $el ) {
			$pos = $this->getPosition( $el );
			if ( $pos < count( $this ) ) {
				return $this[ $pos + 1 ];
			} else {
				return null;
			}
		}
		
		function getPosition( $el ) {
			foreach ( $this as $pos => $node ) {
				if ( $node === $el ) {
					return $pos;
				}
			}
		}
		
		function appendChild( $el ) {
			$this[] = $el;
		}
		
		function insertBefore( $el, $referenceEl = null ) {
			if ( !isset($referenceEl) ) {
				$this->appendChild( $el );
			} else {
				$pos = $this->getPosition( $referenceEl );
				if ( isset($pos) ) {
					parent::__construct( 
						array_splice( $this, $pos, 0, array( $el ) ) 
					);
				} else {
					$this->appendChild( $el );
				}
			}
		}
		
		function replaceChild( $el, $referenceEl ) {
			$pos = $this->getPosition( $referenceEl );
			if ( isset($pos) ) {
				$this[$pos] = $el;
			}
		}	

		function removeChild( $el ) {
			$pos = $this->getPosition( $referenceEl );
			if ( isset($pos) ) {
				parent::__construct(
					array_slice( (array) $this, $pos, 1)
				);
			}
		}
		
	}
	
	class ar_xmlNode extends arBase {
		public $parentNode = null;
		public $nodeValue = '';
		
		function __construct($value, $parentNode = null) {
			$this->nodeValue = $value;
			$this->parentNode = $parentNode;
		}
		
		function __toString() {
			return (string) $this->nodeValue;
		}
		
		function __get( $name ) {
			switch( $name ) {
				case 'previousSibling' :
					if (isset($this->parentNode)) {
						return $this->parentNode->childNodes->getPreviousSibling($this);
					}
				break;
				case 'nextSibling' :
					if (isset($this->parentNode)) {
						return $this->parentNode->childNodes->getNextSibling($this);
					}
				break;
			}
		}
	}
	
	class ar_xmlElement extends ar_xmlNode {
		public $tagName    = null;
		public $attributes = array();
		public $childNodes = null;
		public $parentNode = null;
		private $idCache = array();
		
		function __construct($name, $attributes, $childNodes, $parentNode = null) {
			$this->tagName    = $name;
			$this->attributes = $attributes;
			if (!isset($childNodes)) {
				$childNodes = $this->getNodeList();
			}
			$this->childNodes = $childNodes;
			$this->childNodes->setParentNode( $this );
			$this->parentNode = $parentNode;
		}
		
		public function __updateIdCache($id, $el) {
			$this->idCache[$id] = $el;
			if (isset($this->parentNode)) {
				$this->parentNode->__updateIdCache($id, $el);
			}
		}
		
		function setAttributes( array $attributes ) {
			$oldId = null;
			if (isset($this->attributes['id'])) {
				$oldId = $this->attributes['id'];
			}
			$this->attributes = $attributes + $this->attributes;
			$newId = null;
			if (isset($this->attributes['id'])) {
				$newId = $this->attributes['id'];
			}
			if ($newId !== $oldId) {
				$this->__updateIdCache($newId, $this);
			}
		}

		function setAttribute( $name, $value ) {
			$this->attributes[$name] = $value;
			if ($name=='id') {
				$this->__updateIdCache($value, $this);
			}
		}
		
		function __toString( $indent = '', $current = 0 ) {
			//var_dump($this);
			$indent = ar_xml::$indenting ? $indent : '';
			$result = "\n" . $indent . '<' . ar_xml::name( $this->tagName );
			if ( is_array($this->attributes) ) {
				foreach ( $this->attributes as $name => $value ) {
					$result .= ar_xml::attribute($name, $value, $current);
				}
			} else if ( is_string($this->attributes) ) {
				$result .= ltrim(' '.$this->attributes);
			}
			if ( $this->childNodes instanceof ar_xmlNodes && count($this->childNodes) ) {
				$result .= '>';
				$result .= $this->childNodes->__toString( ar_xml::$indent . $indent );
				if ( substr($result, -1) == ">") {
					$result .= "\n" . $indent;
				}
				$result .= '</' . ar_xml::name( $this->tagName ) . '>';
			} else {
				$result .= ' />';
			}			
			return $result;
		}

		public function getNodeList() {
			$params = func_get_args();
			return call_user_func_array( array( 'ar_xml', 'nodes'), $params );
		}
		
		function __get( $name ) {
			switch( $name ) {
				case 'previousSibling' :
					if (isset($this->parentNode)) {
						return $this->parentNode->childNodes->getPreviousSibling($this);
					}
				break;
				case 'nextSibling' :
					if (isset($this->parentNode)) {
						return $this->parentNode->childNodes->getNextSibling($this);
					}
				break;
				case 'firstChild' :
					if (isset($this->childNodes) && count($this->childNodes)) {
						return $this->childNodes[0];
					}
				break;
				case 'lastChild' :
					if (isset($this->childNodes) && count($this->childNodes)) {
						return $this->childNodes[count($this->childNodes)-1];
					}
				break;
			}
			return $this->getElementsByTagName( $name );
		}
		
		function __set( $name, $value ) {
			$nodeList = $this->__get( $name );
			if (isset($nodeList[0])) {
				$node = $nodeList[0];
				$node->tagName = $value->tagName;
				$node->attributes = $value->attributes;
				$node->childNodes = $value->childNodes;
			}
		}
		
		function getElementsByTagName( $name ) {
			if ( isset( $this->childNodes ) ) {
				return $this->childNodes->getElementsByTagName( $name );
			}
		}
		
		function getElementById( $id ) {
			if (isset($this->idCache[$id])) {
				return $this->idCache[$id];
			}
		}
		
		function appendChild( $el ) {
			$this->childNodes->appendChild( $el );
		}
		
		function insertBefore( $el, $referenceEl = null ) {
			$this->childNodes->insertBefore( $el, $referenceEl );
		}
		
		function replaceChild( $el, $referenceEl ) {
			$this->childNodes->replaceChild( $el, $referenceEl );
		}	

		function removeChild( $el ) {
			$this->childNodes->removeChild( $el );
		}


	}
	
?>