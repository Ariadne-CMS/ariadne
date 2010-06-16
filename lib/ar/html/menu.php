<?php
	/* TODO: 
	- styling: 
		- dropdown: 
			fix hover bg color (is now set on entire submenu instead of single item)
			add hover fg color
			add normal fg color
	*/
	ar_pinp::allow('ar_html_menu');

	class ar_html_menu extends ar_htmlElement {

		private $items   = null;
		private $root    = '';
		private $current = '';
		private $rooturl = '';
		private $filled  = false;
		private $options = array(
			'skipOrphans' => false
		);
		private $prefix = '';
		public $itemTag  = 'li';
		public $listTag  = 'ul';
		public $viewmode = 'list';
		public $template = 'system.get.name.phtml';
		public $css = null;
		
		public function __construct( $attributes = array(), $list = null ) {
			$this->template = ar_content_html::$editMode ? 'system.get.link.phtml' : 'system.get.name.phtml';
			if (!$attributes['class'] && !$attributes['id']) {
				$attributes['class'] = 'menu';
			}
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
			$this->current = $this->root;
			if ( isset($list) ) {
				$this->fill( $list );
			}
			if ($this->attributes['id']) {
				$prefix = '#'.$this->attributes['id'];
			} else {
				$prefix = 'ul.'.$this->attributes['class'];
			}
			$this->prefix = $prefix;
			$this->css = ar_css::stylesheet()
			->import("
				$prefix, $prefix ul {
					list-style: none;
					margin: 0px;
					padding: 0px;
				}

				$prefix li {
					margin: 0px;
					padding: 0px;
				}

				$prefix li a {
					text-decoration: none;
				}
			");
		}

		public function script( $type = '', $matches = array() ) {
			$script = '';
			switch ($type) {
				case 'dropdown' :					
					$script = <<<EOF
function arMenuHover( list ) {
	if ( list ) {
		if ( list[0]=='#' ) {
			var lis = document.getElementById( list.substring(1) ).getElementsByTagName("LI");
		} else {
			if ( list[0] == '.') {
				list = list.substring(1);
			}
			var uls = document.getElementsByTagName( 'UL' );
			var lis = [];
			var re = new RegExp('\\\\b' + list + '\\\\b' );
			for ( var i = uls.length-1; i>=0; i-- ) {
				if ( re.test(uls[i].className) ) {
					var newlis = uls[i].getElementsByTagName( 'LI' );
					for (var ii=newlis.length-1; ii>=0; ii--) {
						lis = lis.concat( newlis[ii] );
					}
				}
			}
		}
		for (var i=lis.length-1; i>=0; i--) {
			lis[i].onmouseover = function() {
				this.className += " menuHover";
			}
			lis[i].onmouseout = function() {
				this.className = this.className.replace(/ menuHover\b/, '');
			}
		}
	}
}

EOF;
				if ( is_string($matches) ) {
					$matches = array( $matches );
				}
				if ( is_array($matches) && count($matches) ) {
					$script .= "if (window.attachEvent && ( !document.documentMode || document.documentMode<8) ) {\n";
					foreach ( $matches as $match ) {
						$script .= " arMenuHover( '$match' );\n";
					}
					$script .= "}\n";
				}
				break;
			}
			return $script;
		}
		
		public function style( $type = '' ) {
			switch ($type) {
				case 'bar' :
					$this->css
						->add($this->prefix.' li', array( 
							'float'            => 'left'
						) )
						->add($this->prefix, array( 
							'overflow'         => 'hidden',
							'padding-right'    => '20px',
						) );
				break;
				case 'tree' :
					$this->css
						->add($this->prefix.' li', array( 'padding' => '0px' ) )
						->add($this->prefix.' li a', array( 'padding-left' => '20px') )
						->add($this->prefix.' li li a', array( 'padding-left' => '40px') )
						->add($this->prefix.' li li li a', array( 'padding-left' => '60px') )
						->add($this->prefix.' li li li li a', array( 'padding-left' => '80px') );
				break;
				case 'crumbs' :
					$this->css->add( $this->prefix.' ul', array( 
							'display'      => 'inline' 
						) )
						->add( $this->prefix.' li', array( 
							'padding-left' => '0px',
							'list-style'   => 'none',
							'display'      => 'inline'
						) )
						->add( $this->prefix.' li li:before', array( 
							'content'      => '"\0020 \00BB \0020"'  // >> or &raquo; character
						) );
				break;
				case 'dropdown' :
					$this->css
						->bind('menuItemWidth', 'auto')
						->bind('menuSubItemWidth', '10em')
						->bind('menuHoverItemBgColor', '#E4E4E4')
						->bind('menuItemBgColor', 'transparent')
						->bind('menuSubItemBgColor', 'white')
						->bind('menuBorderColor', 'transparent')
						->add( $this->prefix, array(
							'line-height'  => '1'
						) )
						->add( $this->prefix.' li', array(
							'width'            => 'var(menuItemWidth)',
							'float'            => 'left',
							'position'         => 'relative',
							'background-color' => 'var(menuItemBgColor)'
						) )
						->add( $this->prefix.' li a', array(
							'width'            => 'var(menuItemWidth)',
							'padding'          => '0px 10px 0px 0px'
						) )
						->add( $this->prefix.' li li a', array(
							'width'            => 'var(menuSubItemWidth)',
							'display'          => 'block'
						) )
						->add( $this->prefix.' li:hover a', array(
							'background-color' => 'var(menuHoverItemBgColor)'
						) )
						->add( $this->prefix.' li.menuHover a', array(
							'background-color' => 'var(menuHoverItemBgColor)'
						) )
						->add( $this->prefix.' li ul', array(
							'width'            => 'var(menuSubItemWidth)',
							'position'         => 'absolute',
							'left'             => '-999em',
							'background-color' => 'var(menuSubItemBgColor)',
							'border'           => '1px solid var(menuBorderColor)'
						) )
						->add( $this->prefix.' li li a', array(
							'width'        => 'var(menuSubItemWidth)',
							'padding'      => '0px'
						) )
						->add( $this->prefix.' li:hover ul', array(
							'left'         => 'auto'
						) )
						->add( $this->prefix.' li.menuHover ul', array(
							'left'         => '0px',
							'top'          => '1em'
						) )
						->add( $this->prefix.' li ul ul', array(
							'margin'       => '-1em 0 0 var(menuSubItemWidth)'
						) )
						->add( $this->prefix.' li:hover ul ul', array(
							'left'         => '-999em'
						) )
						->add( $this->prefix.' li.menuHover ul ul', array(
							'left'         => '-999em'
						) )
						->add( $this->prefix.' li li:hover ul', array(
							'left'         => 'auto'
						) )
						->add( $this->prefix.' li li.menuHover ul', array(
							'left'         => 'var(menuSubItemWidth)',
							'top'          => '0px'
						) )
						->add( $this->prefix.' li:hover ul ul ul', array(
							'left'         => '-999em'
						) )
						->add( $this->prefix.' li.menuHover ul ul ul', array(
							'left'         => '-999em'
						) )
						->add( $this->prefix.' li li li:hover ul', array(
							'left'         => 'auto'
						) )
						->add( $this->prefix.' li li li.menuHover ul', array(
							'left'         => 'auto'
						) );
				break;
				case 'tabs' :
					$this->css
						->bind('menuBgColor',           'transparent')
						->bind('menuItemBgColor',       '#E4E4E4')
						->bind('menuActiveItemBgColor', 'white')
						->bind('menuBorderColor',       'black')
						->add($this->prefix.' li', array( 
							'float'            => 'left',
							'background-color' => 'var(menuItemBgColor)',
							'border'           => '1px solid var(menuBorderColor)',
							'margin'           => '0px 5px -1px 5px',
							'padding'          => '0px 5px',
							'height'           => '1.5em'
						) )
						->add($this->prefix.' li.menuCurrent', array(
							'background-color' => 'var(menuActiveItemBgColor)',
							'border-bottom'    => '1px solid var(menuActiveItemBgColor)'
						) )
						->add($this->prefix, array( 
							'height'           => '1.5em',
							'padding'          => '0px 20px 1px 0px',
							'border-bottom'    => '1px solid var(menuBorderColor)'
						) );
				break;
			}
			return $this->css;
		}
		
		public function root( $url, $path='/' ) {
			if ($this->root == $this->current) {
				$this->current = $path;
			}
			$this->rooturl = $url;
			$this->root    = $path;
		}
		
		public function template( $template ) {
			$this->template = $template;
			return $this;
		}

		public function toString( $indent = '', $current = 0 ) {
			if ( !$this->filled ) {
				// do a default menu.
				$this->bar();
			}
			return parent::toString( $indent, $current );
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

		private function _getItemInfo( $item, $key, $parent, $current ) {
			if ( $item instanceof ar_htmlElement ) {
				if ($item->attributes['href']) {
					$url = $item->attributes['href'];
				} else {
					$url = null;
				}
				$item = array( 'node' => $item, 'url' => $url );
			}
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
					$linkAttributes['class'][] = $item['class'];
				}
				if ( isset($item['id']) ) {
					$linkAttributes['id'] = $item['id'];
				}
				if ( ($item['path']==$current) || ($item['url'] == $current) ) {
					$linkAttributes['class']['menuCurrent'] = 'menuCurrent';
					if (!is_array($item['attributes']['class'])) {
						$item['attributes']['class'] = array( $item['attributes']['class'] );
					}
					$item['attributes']['class']['menuCurrent'] = 'menuCurrent';
				}
				$item['node'] = ar_html::tag( $item['tagName'], $item['attributes'], 
					ar_html::tag( 'a', $linkAttributes, $item['name'])
				);
			} else {
				if ( ($item['path']==$current) || ($item['url'] == $current) ) {
					$link = $item['node']->a[0];
					if ($link) {
						$link->setAttribute('class', array( 'menuCurrent' => 'menuCurrent') );
					}
					$item['node']->setAttribute('class', array( 'menuCurrent' => 'menuCurrent') );
				}
			}
			return $item;
		}
		
		private function _fillFromArray( $list, $current, $parent = '[root]' ) {
			// first enter all nodes into the items list
			// then rearrange them into parent/child relations
			// otherwise you must always have the parent in the list before any child
			
			foreach ( $list as $key => $item ) {
				$itemInfo = $this->_getItemInfo( $item, $key, $parent, $current );
				$itemNode = $itemInfo['node'];
				if ($parent=='[root]') {
					$this->items[$itemInfo['url']] = $itemNode;
				}
				if ($itemInfo['children']) {
					$this->_fillFromArray( $itemInfo['children'], $current, $itemInfo['url'] );
				}
			}
			foreach ( $this->items as $url => $itemNode ) {
				if ( $url != '[root]' ) { // do not remove, prevents infinite loop
					if ( $parent == '[root]' ) {
						if ($url == $this->rooturl) {
							$parentNode = $this;
						} else {
							$oldparent = '';
							$newparent = dirname( $url ).'/';
							if ( !$this->options['skipOrphans'] ) {
								while ($newparent!=$oldparent && !isset($this->items[$newparent]) && $newparent!=$this->rooturl) {
									$oldparent = $newparent;
									$newparent = dirname( $newparent ).'/';
								}
							}
							if ( isset($this->items[$newparent]) ) {
								$parentNode = $this->items[$newparent]->ul[0];
								if (!isset($parentNode)) {
									$parentNode = $this->items[$newparent]->appendChild( ar_html::tag( $this->listTag ) );
								}
							} else if ($newparent == $this->rooturl) {
								$parentNode = $this;
							} else if (!$this->options['skipOrphans']) {
								$parentNode = $this;
							} else {
								$parentNode = null;
							}
						}
					} else if ( isset($this->items[$parent]) ) {
						$parentNode = $this->items[$parent];
					} else if ( !$this->options['skipOrphans'] ) {
						$parentNode = $this;
					} else {
						$parentNode = null;
					}
					if ($parentNode) {
						$parentNode->appendChild( $itemNode );
					}
				}
			}
		}
		
		public function fill( $list, $options = array() ) {
			$current = $options['current'] ? $options['current'] : $this->current;
			$root    = $options['root'] ? $options['root'] : $this->root;
			if ( ($list instanceof ar_storeFind) || ($list instanceof ar_storeParents) ) {
				$list = $list->call( $this->template, array( 'current' => $current, 'root' => $root ) );
			}
			$this->options = $options;
			if ( is_array($list) ) {
				$this->_fillFromArray( $list, $current );
			}
			$this->filled = true;
			return $this;
		}
		
		public function stripe( $options = array() ) {
			$options += array(
				'menuStriping'         => ar::listPattern( 'menuFirst .*', '(menuOdd menuEven?)*', '.* menuLast' ),
				'menuStripingContinue' => false
			);
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
		
		public function autoID( $root = 'menu', $element = null ) {
			// create unique id's per list item
			if (!isset($element) ) {
				$element = $this;
			}
			if (!$element->attributes['id']) {
				$element->setAttribute( 'id', $root.'-ul' );
			}
			$list    = $element->li;
			$counter = 0;
			foreach ($list as $li) {
				$id = $root.'-'.$counter++;
				if ( !$li->attributes['id'] ) {
					$li->setAttribute( 'id', $id );
				}
				$ul = $li->ul;
				if (count($ul)) {
					$this->autoID( $id, $ul[0] );
				}
			}
			return $this;
		}
		
		public function childIndicators() {
			$list = $this->getElementsByTagName('ul');
			foreach( $list as $ul) {
				$ul->parentNode->setAttribute('class', array('menuChildIndicator' => 'menuHasChildren'));
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
				'siblings'    => true,
				'children'    => true,
				'top'         => $this->root,
				'current'     => $this->current,
				'skipTop'     => false,
				'skipOrphans' => true,
				'maxDepth'    => 0
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
			$maxDepth = (int)$options['maxDepth'];
			if ($maxDepth>0) {
				$match = " and object.path !~ '".$top."%/";
				while ($maxDepth>0) {
					$match .= "%/";
					$maxDepth--;
				}
				$match .= "'";
				$query .= $match;
			}
			$this->fill( ar::get($top)->find("object.implements = 'pdir' and object.priority>=0".$query), $options );
			return $this;
		}
		
		public function bar( $options = array() ) {
			$this->viewmode = 'list';
			$options += array(
				'top'     => $this->root,
				'current' => $this->current
			);
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
				'top'         => $this->root,
				'skipTop'     => true,
				'maxDepth'    => 0,
				'skipOrphans' => true
			);
			$top     = $options['top'];
			if ( !isset($top) ) {
				$top = $this->root;
			}
			$query = "object.implements='pdir' and object.priority>=0";
			if ($options['skipTop']) {
				$query .= " and object.path != '".$top."'";
			}
			$maxDepth = (int)$options['maxDepth'];
			if ($maxDepth>0) {
				$match = " and object.path !~ '".$top."%/";
				while ($maxDepth>0) {
					$match .= "%/";
					$maxDepth--;
				}
				$match .= "'";
				$query .= $match;
			}
			$this->fill( ar::get( $top )->find( $query ), $options );
			return $this;
		}
		
		public function crumbs( $options = array() ) {
			$this->viewmode = 'crumbs';
			$options += array(
				'current' => $this->current,
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