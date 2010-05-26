<?php

	ar_pinp::allow('ar_html_menu');

	// TODO: bar() must also specify option to fill() to generate 1 ul with many li's, not nested
	// independent of path/url.
	
	class ar_html_menu extends ar_htmlElement {

		private $items   = null;
		private $root    = '';
		private $rooturl = '';
		private $filled  = false;
		private $options = array();
		public $itemTag  = 'li';
		public $listTag  = 'ul';
		public $viewmode = 'list';
		public $template = 'system.get.name.phtml';
		
		public function __construct( $attributes = array(), $list = null ) {
			parent::__construct( 'ul', $attributes, null );
			$this->items['[root]'] = $this;
			//FIXME: remove getContext stuff by adding make_url to ar::something
			if ( class_exists('pobject') ) {
				$context = pobject::getContext();
				if ( isset($context) && isset($context['arCurrentObject']) ) {
					$me            = $context["arCurrentObject"];
					$this->root    = $me->path;
					$this->rooturl = $me->make_url();
				}
			}
			if ( !isset($this->root) ) {
				$this->root    = '/';
				$this->rooturl = '/';
			}
			if ( isset($list) ) {
				$this->fill( $list );
			}
		}
		
		public function root( $url, $path='/' ) {
			$this->rooturl = $url;
			$this->root    = $path;
		}
		
		public function template( $template ) {
			$this->template = $template;
			return $this;
		}

		public function __toString( $indent = '', $current = 0 ) {
			if ( !$this->filled ) {
				// do a default menu.
				$this->bar();
			}
			return parent::__toString( $indent, $current );
		}
		
		private function _makeURL( $path, $parent ) {
/*
			- ?bla=bla    -> parent url + argumenten
			- #bla        -> parent url + fragment
			- /pad        -> absoluut pad -> make_url
			- http://.../ -> url
			  ftp://
			  mailto:auke@muze.nl
			  [a-z]+:
			- rest is een pad -> make_url() op aanroepen, maar relatief t.o.v. parent node?
			root node heeft de url van het huidige object, ook al is het geen link, relatieve
			childnodes gaan dus relatief ten opzichte daarvan. Tenzij andere rootnode opgegeven
			wordt.
*/			switch( $path[0] ) {
				case '?' :
					$qpos = strpos( '?', $parent );
					if (false!==$qpos) {
						$url   = substr($parent, $qpos);
						$query = substr($parent, $qpos+1);
						$fpos  = strpos( '#', $query );
						if (false!==$fpos) {
							$fragment = substr($query, $fpos);
							$query    = substr($query, 0, $fpos);
						} else {
							$fragment = '';
						}
						parse_str($query, $params);
						parse_str(substr($path, 1), $newparams);
						$params = array_merge_recursive( $params, $newparams );
						$url   .= '?' . http_build_query( $params );
					} else {
						$url   .= $path;
					}
				break;
				case '#' :
					$fpos = strpos( '#', $parent );
					if ( false !== $fpos ) {
						$url = substr($parent, $fpos);
					} else {
						$url = $parent;
					}
					$url .= $path;
				break;
				case '/' :
					if ( substr( $path, 0, strlen( $this->root ) ) == $this->root ) {
						$path = substr($path, strlen($this->root));
					} else if ( substr( $this->rooturl, -1 ) == '/' ) {
						$path = substr($path, 1);
					}
					$url = $this->rooturl . $path;
				break;
				default :
					if ( preg_match( '/^[a-z]+:/', $path ) ) { // url
						$url = $path;
					} else { // relative path
						$url = $parent . $path;
					}
				break;
			}
			return $url;
		}

		private function _getItemInfo( $item, $key, $parent ) {
			if (!is_array($item)) {
				$item = array( 'name' => $item );
			}
			if ( !isset($item['path']) ) {
				$item['path'] = $key;
			}
			if ( !isset($item['url']) ) {
				$item['url'] = $this->_makeURL( $item['path'], $parent );
			}
			if ( !isset($item['node']) ) {
				if ( !isset($item['tagName']) ) {
					$item['tagName'] = $this->itemTag;
				}
				if ( !isset($item['attributes']) ) { 
					//FIXME: ['attributes']['a'] / ['li'] / ['ul']
					// rest of ['attributes'] = ['attributes']['a']
					$item['attributes'] = array();
				}
				$linkAttributes = array( 'href' => $item['url'] );
				if ( isset($item['title']) ) {
					$linkAttributes['title'] = $item['title'];
				}
				if ( isset($item['class']) ) {
					$linkAttributes['class'] = $item['class'];
				}
				$item['node'] = ar_html::tag( $item['tagName'], $item['attributes'], 
					ar_html::tag( 'a', $linkAttributes, $item['name'])
				);
			}
			return $item;
		}
		
		private function _fillFromArray( $list, $parent = '[root]' ) {
			foreach ( $list as $key => $item ) {
				$itemInfo = $this->_getItemInfo( $item, $key, $parent );
				$itemNode = $itemInfo['node'];
				if ($itemInfo['children']) {
					$this->_fillFromArray( $itemInfo['children'], $itemInfo['url'] );
				}
				if ( $parent == '[root]' ) {
					$newparent = dirname( $itemInfo['url'] ).'/';
					if (isset($this->items[$newparent])) {
						$parentNode = $this->items[$newparent]->ul[0];
						if (!isset($parentNode)) {
							$parentNode = $this->items[$newparent]->appendChild( ar_html::tag( $this->listTag ) );
						}
					} else {
						$newparent  = $parent;
						$parentNode = $this;
					}
				} else {
					$parentNode = $this;
					$newparent  = $parent;
				}
				$parentNode->appendChild( $itemNode );
				$this->items[$itemInfo['url']] = $itemNode;
			}
		}
		
		public function fill( $list, $options = array() ) {
			$options += array(
				'menuStriping'         => ar::listPattern( 'menuFirst .*', '(menuOdd menuEven?)*', '.* menuLast' ),
				'menuStripingContinue' => false
			);
			if ( ($list instanceof ar_storeFind) || ($list instanceof ar_storeParents) ) {
				$list = $list->call( $this->template, array( 'current' => $this->current, 'root' => $this->root ) );
			}
			if ( is_array($list) ) {
				$this->_fillFromArray( $list );
			}
			$this->options = $options;
			$this->stripe();
			$this->filled = true;
			return $this;
		}
		
		public function stripe( $options = array() ) {
			$options += $this->options;
			if ( $options['menuStriping'] ) {
				if ( $options['menuStripingContinue'] ) {
					$this->getElementsByTagName('li')->setAttribute('class', array(
						'menuStriping' => $options['menuStriping']
					) );
				} else {
					$this->childNodes->setAttribute( 'class', array(
						'menuStriping' => $options['menuStriping']
					) );
					$uls = $this->getElementsByTagName('ul');
					foreach( $uls as $ul ) {
						$ul->childNodes->setAttribute( 'class', array(
							'menuStriping' => $options['menuStriping']
						) );
					}
				}
			}
			return $this;
		}
		
		public function levels( $depth, $start = 0, $root = null ) {
			// add level classes to the ul/li tags, level-0, level-1, etc.
			if ( $depth == 0 ) {
				return $this;
			}
			if (!isset($root)) {
				$root = $this;
			}
			if ( $root instanceof ar_htmlElement ) {
				$root = ar_html::nodes( $root );
			}
			if ($root instanceof ar_htmlNodes && count($root) ) {
				$root->setAttribute( 'class', array('menuLevels' => 'menuLevel-'.$start) );
				foreach( $root as $element ) {
					$element->childNodes->setAttribute( 'class', array( 'menuLevels' => 'menuLevel-'.$start ) );
					$this->levels( $depth-1, $start+1, $element->li->ul );
				}
			}
			return $this;
		}
		
		public function current( $path ) {
			$this->current = $path;
			return $this;
		}
		
		public function tree( $options = array() ) {
			$this->viewmode = 'tree';
			$options += array(
				'siblings' => true,
				'children' => true,
				'top'      => $this->root,
				'current'  => $this->root,
				'skipTop'  => false
			);
			$current = $options['current'];
			$top     = $options['top'];
			$query   = "";
			if (!isset($top)) {
				$top = $current;
			}
			if ($top[strlen($top)-1] != '/') {
				$top = $top.'/';
			}
			if ($options['siblings'] && !$options['children']) {
				$current = dirname($current).'/';
			} else if (!$options['siblings'] && $options['children']) {
				$query .= "object.parent='$current' or ";
			}
			while ( 
				( substr( $current, 0, strlen( $top ) ) === $top )
				&& ar::exists( $current )
			) {
				if ($options['siblings']) {
					$query .= "object.parent='$current' or ";
				} else {
					$query .= "object.path='$current' or ";
				}
				$current = dirname($current).'/';
			}
			if ( !$options['skipTop'] ) {
				$query .= "object.path='$top' or ";
			}
			if ($query) {
				$query = " and ( " . substr($query, 0, -4) . " )";
			}
			$this->fill( ar::get($top)->find("object.implements = 'pdir' and object.priority>=0".$query), $options );
			return $this;
		}
		
		public function bar( $options = array() ) {
			$this->viewmode = 'list';
			$options += array(
				'top'     => $this->root,
				'current' => $this->root
			);
			$current = $options['current'];
			$top     = $options['top'];
			if ( !isset($top) ) {
				$top = $this->root;
			}
			$query = ar::get( $top )->find( "object.implements='pdir' and object.priority>=0 and object.parent = '$top'" );
			$this->fill( $query, $options);
			return $this;
		}
		
		public function sitemap( $options = array() ) {
			$this->viewmode = 'sitemap';
			$options += array(
				'top'     => $this->root
			);
			$top     = $options['top'];
			if ( !isset($top) ) {
				$top = $this->root;
			}
			$query = ar::get( $top )->find( "object.implements='pdir' and object.priority>=0" );
			$this->fill( $query, $options );
			return $this;
		}
		
		public function crumbs( $options = array() ) {
			$this->viewmode = 'crumbs';
			$options += array(
				'current' => $this->root,
				'top'     => $this->root,
				'menuStripingContinue' => true
			);
			$top     = $options['top'];
			$current = $options['current'];
			if ( !isset($top) ) {
				$top = $this->root;
			}
			$query = ar::get( $current )->parents()->top( $top );
			$this->fill( $query, $options );
			return $this;
		}
		
	}

?>