<?php
	ar_pinp::allow( 'ar_css' );
	ar_pinp::allow( 'ar_cssStyleSheet' );
	ar_pinp::allow( 'ar_cssSearch' );
	ar::load('html');
	
	class ar_css extends arBase {
		
		public static function stylesheet() {
			return new ar_cssStyleSheet( );
		}
		
	}
	
	class ar_cssStyleSheet extends ar_htmlElement {
		// FIXME: make the css content available as nodeValue of a child ar_htmlNode (or a list of nodes?)
		// create a ar_cssRule which implements or extends ar_htmlNode?
		private $cssText   = '';
		private $variables = array();
		public  $rules     = null;
		
		public function __construct($tagName = 'style', $attributes = array(), $childNodes = null, $parentNode = null) {
			$this->rules   = new ar_cssRules( $this );
			if ( !$attributes ) {
				$attributes = array();
			}
			if ( !$attributes['type'] ) {
				$attributes['type'] = 'text/css';
			}
			parent::__construct( $tagName, $attributes, null, $parentNode );
		}
		
		public function rule( $selector, $styles ) {
			$this->rules[$selector] = new ar_cssStyles( $this->rules, $styles );
			return $this;
		}
		
		public function find( $search ) {
			$found = new ar_cssRules( $this );
			foreach ( $this->rules as $rule => $styles ) {
				if ( preg_match( $search, $rule ) ) {
					$found[$rule] = $styles;
				}
			}
			return new ar_cssSearch( $this, $search, $found );
		}
		
		public function add( $selector, $styles ) {
			$this->rules[$selector] = new ar_cssStyles(
				$this->rules,
				array_merge( 
					(array) $this->rules[$selector],
					(array) $styles
				)
			);
			return $this;
		}
		
		public function delete( $selector, $styles = null ) {
			if ( isset($styles) ) {
				foreach( $styles as $style ) {
					unset( $this->rules[$selector][$style] );
				}
			} else {
				unset( $this->rules[$selector] );
			}
			return $this;
		}
		
		public function copy( $selector, $newselector ) {
			$this->add( $newselector, $this->rules[$selector] );
		}
		
		public function import( $cssText ) {
			// ignore comments /* */ but keep them in
			$ruleRE  = '/([^{]*)\{(.*)\}/isU';
			$styleRE = '/([^:]+)\:(.+)(;|$)/isU';
			while ( preg_match( $ruleRE, $cssText, $matches ) ) {
				$rule       = $matches[1];
				$stylesText = $matches[2];
				while ( preg_match( $styleRE, $stylesText, $styleMatches ) ) {
					$styles[ $styleMatches[1] ] = $styleMatches[2];
					$stylesText = str_replace( $styleMatches[0], '', $stylesText );
				}
				$this->add( $rule, $styles );
				$styles = array();
				$cssText = str_replace( $matches[0], '', $cssText);
			}
			$this->cssText = $cssText;
			return $this;
		}

		public function rename( $selector, $newselector ) {
			$this->rules[$newselector] = $this->rules[$selector];
			unset( $this->rules[$selector] );
		}
		
		public function getVariable( $name ) {
			return $this->variables[$name] ? $this->variables[$name] : null;
		}
		
		public function bind( $variable, $value = null ) {
			if ( is_array($variable) ) {
				$this->variables = $variable + $this->variables;
			} else {
				$this->variables[$variable] = $value;
			}
			return $this;
		}
		
		public function __toString() {
			return (string) ar_html::tag( 'style', $this->attributes, (string) $this->rules );
		}
	}
	
	class ar_cssSearch extends arBase {
		private $styleSheet = null;
		private $search     = null;
		private $rules      = null;
	
		public function __construct( $styleSheet, $search, $rules ) {
			$this->styleSheet = $styleSheet;
			$this->search     = $search;
			$this->rules      = new ar_cssRules($styleSheet, $rules);
		}

		public function __toString() {
			$this->apply();
			return $this->styleSheet->__toString();
		}
		
		public function rule( $newstyles ) {
			foreach ( $this->rules as $rule => $styles ) {
				$this->rules[$rule] = new ar_cssStyles( $this->rules, $newstyles);
			}
			return $this;
		}
		
		public function delete( $oldstyles = null ) {
			foreach ( $this->rules as $rule => $styles ) {
				if (isset($oldstyles)) {
					foreach ( $oldstyles as $style ) {
						$this->rules[$rule][$style] = null;
					}
				} else {
					$this->rules[$rule] = null;
				}
			}
			return $this;
		}

		public function add( $newstyles ) {
			foreach ( $this->rules as $rule => $styles ) {
				$this->rules[$rule] = new ar_cssStyles( $this->rules, array_merge( (array) $styles, (array) $newstyles ) );
			}
			return $this;
		}
		
		public function rename( $newselector ) {
			foreach ( $this->rules as $rule => $styles ) {
				$newrule = preg_replace( $rule, $this->search, $newselector );
				$this->rules[$newrule] = $styles;
				$this->rules[$rule]    = null;
			}
			return $this;
		}
		
		public function apply() {
			foreach ( $this->rules as $rule => $styles ) {
				if ( isset($styles) ) {
					$this->styleSheet->rule( $rule, $styles );
				} else {
					$this->styleSheet->delete( $rule );
				}
			}
			return $this->styleSheet;
		}
	}
	
	interface ar_cssRulesInterface {
	}
	
	class ar_cssRules extends ArrayObject implements ar_cssRulesInterface {
		private $styleSheet = null;
		
		public function __construct( $styleSheet, $rules = array() ) {
			$this->styleSheet = $styleSheet;
			parent::__construct( (array) $rules );
		}
		
		public function __toString() {
			$result = '';
			foreach( $this as $rule => $style ) {
				$result .= "\n" . trim($rule) . " {\n" . $style . "}\n";
			}
			return $result;
		}

		public function getVariable( $name ) {
			return $this->styleSheet->getVariable( $name );
		}
	}
	
	interface ar_cssStylesInterface {
	}
	
	class ar_cssStyles extends ArrayObject implements ar_cssStylesInterface {

		private $rules = null;
		
		public function __construct( $rules, $styles = array() ) {
			$this->rules = $rules;
			parent::__construct( (array) $styles );
		}
		
		public function __toString() {
			$result = '';
			foreach ($this as $style => $value ) {
				if ( $value ) {
					while ( preg_match('/\b(var\((.*)\))/', $value, $matches) ) {
						$var   = $this->getVariable( $matches[2] );
						if ( isset($var) ) {
							$value = str_replace( $matches[1], $var, $value );
						}
					}
					$result .= "\t" . trim($style) . ": " . trim($value) . ";\n";
				}
			}
			return $result;
		}

		protected function getVariable( $name ) {
			return $this->rules->getVariable( $name );
		}
	}
?>