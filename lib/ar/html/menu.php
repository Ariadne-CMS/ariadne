<?php
	/* TODO: 
	- styling: 
		- dropdown: 
			fix hover bg color (is now set on entire submenu instead of single item)
			add hover fg color
			add normal fg color
	*/
	ar_pinp::allow('ar_html_menu');

	class ar_html_menu extends arBase {
	
		public static function bar( $options = array() ) {
			$parent = $options['top'];
			if (!$parent) {
				$parent = ar::context()->getPath();
			}
			return ar::get( $parent )->find( "object.implements='pdir' and object.priority>=0 and object.parent = '$parent'" );
		}
	
		public static function tree( $options = array() ) {
			$current = $options['current'];
			if (!$current) {
				$current = ar::context()->getPath();
			}
			$top = $options['top'];
			if (!$top) {
				$top = ar::currentSite( $current );
			}
			$options += array(
				'siblings'    => true,
				'children'    => true,
				'current'     => null,
				'skipTop'     => false,
				'maxDepth'    => 0
			);
			$query   = "";
			if ($top[strlen($top)-1] != '/') {
				$top = $top.'/';
			}
			if ($options['siblings'] && !$options['children']) {
				$current = dirname($current).'/';
			} else if (!$options['siblings'] && $options['children'] ) {
				$query .= "object.parent='".$current."' or ";
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
				$query .= " and object.path !~ '" . $top . str_repeat( '%/', $maxDepth + 1 ) . "'";
			}
			return ar::get($top)->find("object.implements = 'pdir' and object.priority>=0".$query);
		}
	
		public static function sitemap( $options = array() ) {
			$top = $options['top'];
			if (!$top) {
				$top = ar::currentSite( ar::context()->getPath() );
			}
			$options += array(
				'skipTop'     => true,
				'maxDepth'    => 0
			);
			$query = "object.implements='pdir' and object.priority>=0";
			if ($options['skipTop']) {
				$query .= " and object.path != '".$top."'";
			}
			$maxDepth = (int)$options['maxDepth'];
			if ($maxDepth>0) {
				$query .= " and object.path !~ '" . $top . str_repeat( '%/', $maxDepth + 1 ) . "'";
			}
			return ar::get( $top )->find( $query );
		}
		
		public static function crumbs( $options = array() ) {
			$current = $options['current'];
			if (!$current) {
				$current = ar::context()->getPath();
			}
			$top = $options['top'];
			if (!$top) {
				$top = ar::currentSite( $current );
			}
			$options += array(
				'current' => $current,
				'top'     => $top
			);
			$current = $options['current'];
			if (!$current) {
				$current = $top;
			}
			if ( !isset($top) ) {
				$top = $this->root;
			}
			return ar::get( $current )->parents()->top( $top );
		}

		public static function el( $tagName, $attributes, $childNodes = null, $parentNode = null ) {
			return new ar_html_menuElement( $tagName, $attributes, $childNodes, $parentNode );
		}
		
		public static function element( $data, $attributes, $options ) {
			return self::el( $data, $attributes, $options );
		}
		
	}

	class ar_html_menuElement extends ar_htmlElement {

		private $items   = null;
		private $root    = '';
		private $current = '';
		private $rooturl = '';
		private $filled  = false;
		private $stripeOptions = false;
		private $levelOptions  = false;
		private $autoIDOptions  = false;
		private $options = array(
			'skipOrphans' => false,
			'itemTag'     => 'li',
			'listTag'     => 'ul'
		);
		private $prefix = '';
		public $viewmode = 'list';
		public $template = 'system.get.link.phtml';
		public $css = null;

		public function __construct( $tagName = 'ul', $attributes = array(), $childNodes = null, $parentNode = null ) {
			if (!$attributes['class'] && !$attributes['id']) {
				$attributes['class'] = 'menu';
			}
			if (!$tagName) {
				$tagName = 'ul';
			}
			$this->options['listTag'] = $tagName;
			switch ($tagName) {
				case 'ul':
				case 'ol':
					$this->options['itemTag'] = 'li';
				break;
				case 'dl':
					$this->options['itemTag'] = 'dt';
				break;
				default:
					$this->options['itemTag'] = $tagName;
				break;
			}
			if ( ! ( $childNodes instanceof ar_htmlNodes ) ) {
				$data = $childNodes;
				$childNodes = null;
			}
			parent::__construct( $tagName, $attributes, $childNodes, $parentNode );
			$this->items['[root]'] = $this;
			$context = ar::context();
			$me = $context->getObject();
			if ( $me ) {
				$this->root    = $me->currentsite();
				$this->rooturl = $me->make_url( $this->root );
			}
			if ( !isset($this->root) ) {
				$this->root    = '/';
				$this->rooturl = '/';
			}
			$this->current = $this->root;
			$listTag = $this->options['listTag'];
			$itemTag = $this->options['itemTag'];
			if ($this->attributes['id']) {
				$prefix = '#'.$this->attributes['id'];
			} else {
				$prefix = $listTag . '.' . $this->attributes['class'];
			}
			$this->prefix = $prefix;
			$this->css = ar_css::stylesheet()
			->import("
				$prefix, $prefix $listTag {
					list-style: none;
					margin: 0px;
					padding: 0px;
				}

				$prefix $itemTag {
					margin: 0px;
					padding: 0px;
				}

				$prefix $itemTag a {
					text-decoration: none;
				}
			");
		}

		public function setAttribute( $attribute, $value ) {
			parent::setAttribute( $attribute, $value );
			if ($this->attributes['id']) {
				$this->prefix = '#'.$this->attributes['id'];
			} else {
				$this->prefix = $this->options['listTag'] . '.' . $this->attributes['class'];
			}
			return $this;
		}
		
		public function script( $type = '', $matches = array() ) {
			$script = '';
			switch ($type) {
				case 'dropdown' :	
					$listTagUp = strtoupper( $this->options['listTag'] );
					$itemTagUp = strtoupper( $this->options['itemTag'] );
					$script = <<<EOF
function arMenuHover( list ) {
	if ( list ) {
		if ( list[0]=='#' ) {
			var lis = document.getElementById( list.substring(1) ).getElementsByTagName("{$itemTagUp}");
		} else {
			if ( list[0] == '.') {
				list = list.substring(1);
			}
			var uls = document.getElementsByTagName( '{$listTagUp}' );
			var lis = [];
			var re = new RegExp('\\\\b' + list + '\\\\b' );
			for ( var i = uls.length-1; i>=0; i-- ) {
				if ( re.test(uls[i].className) ) {
					var newlis = uls[i].getElementsByTagName( '{$itemTagUp}' );
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
			$itemTag = $this->options['itemTag'];
			$listTag = $this->options['listTag'];
			$prefix = $this->prefix;
			switch ($type) {
				case 'bar' :
					$this->css->import("
						$prefix $itemTag { 
							float:            : left;
						}
						$prefix $listTag $listTag {
							float:            : left;
							padding:          : 0px;
							margin:           : 0px;
						}
						$prefix { 
							overflow:         : hidden;
							padding-right:    : 20px;
						}");
				break;
				case 'tree' :
					$this->css->import("
						$prefix $itemTag {
							padding: 0px;
						}
						$prefix $itemTag a { 
							padding-left: 20px;
						}
						$prefix $itemTag $itemTag a {
							padding-left: 40px;
						}
						$prefix $itemTag $itemTag $itemTag a {
							padding-left: 60px;
						}
						$prefix $itemTag $itemTag $itemTag $itemTag a {
							padding-left: 80px;
						}
					");
					break;
				case 'crumbs' :
					$this->css->import("
						$prefix $listTag { 
							display      : inline; 
						}
						$prefix $itemTag { 
							padding-left : 0px;
							list-style   : none;
							display      : inline;
						}
						$prefix $itemTag $itemTag:before { 
							content      : \"\\0020 \\00BB \\0020\"  /* >> or &raquo character */
						}
					");
				break;
				case 'dropdown' :
					$this->css
						->bind('menuItemWidth', 'auto')
						->bind('menuSubItemWidth', '10em')
						->bind('menuHoverItemBgColor', '#E4E4E4')
						->bind('menuItemBgColor', 'transparent')
						->bind('menuSubItemBgColor', 'white')
						->bind('menuBorderColor', 'transparent')
						->import("
							$prefix {
								line-height  : 1px;
							}
							$prefix $itemTag {
								width            : var(menuItemWidth);
								float            : left;
								position         : relative;
								background-color : var(menuItemBgColor);
							}
							$prefix $itemTag a {
								width            : var(menuItemWidth);
								padding          : 0px 10px 0px 0px;
							}
							$prefix $itemTag $itemTag a {
								width            : var(menuSubItemWidth);
								display          : block;
							}
							$prefix $itemTag:hover a {
								background-color : var(menuHoverItemBgColor);
							}
							$prefix $itemTag.menuHover a {
								background-color : var(menuHoverItemBgColor);
							}
							$prefix $itemTag $listTag {
								width            : var(menuSubItemWidth);
								position         : absolute;
								left             : -999em;
								background-color : var(menuSubItemBgColor);
								border           : 1px solid var(menuBorderColor);
							}
							$prefix $itemTag $itemTag a {
								width        : var(menuSubItemWidth);
								padding      : 0px;
							}
							$prefix $itemTag:hover $listTag {
								left         : auto;
							}
							$prefix $itemTag.menuHover $listTag {
								left         : 0px;
								top          : 1em;
							}
							$prefix $itemTag $listTag $listTag {
								margin       : -1em 0 0 var(menuSubItemWidth);
							}
							$prefix $itemTag:hover .$listTag $listTag {
								left         : -999em;
							}
							$prefix $itemTag.menuHover $listTag $listTag {
								left         : -999em;
							}
							$prefix $itemTag $itemTag:hover $listTag {
								left         : auto
							}
							$prefix $itemTag $itemTag.menuHover $listTag {
								left         : var(menuSubItemWidth);
								top          : 0px;
							}
							$prefix $itemTag:hover $listTag $listTag $listTag {
								left         : -999em;
							}
							$prefix $itemTag.menuHover $listTag $listTag $listTag {
								left         : -999em;
							}
							$prefix $itemTag $itemTag $itemTag:hover $listTag {
								left         : auto;
							}
							$prefix $itemTag $itemTag $itemTag.menuHover $listTag {
								left         : auto;
							) 
						");
				break;
				case 'tabs' :
					$this->css
						->bind('menuBgColor',           'transparent')
						->bind('menuItemBgColor',       '#E4E4E4')
						->bind('menuActiveItemBgColor', 'white')
						->bind('menuBorderColor',       'black')
						->import("
							$prefix $itemTag { 
								float            : left;
								background-color : var(menuItemBgColor);
								border           : 1px solid var(menuBorderColor);
								margin           : 0px 5px -1px 5px;
								padding          : 0px 5px;
								height           : 1.5em;
							}
							$prefix $itemTag.menuCurrent {
								background-color : var(menuActiveItemBgColor);
								border-bottom    : 1px solid var(menuActiveItemBgColor);
							}
							$prefix {
								height           : 1.5em;
								padding          : 0px 20px 1px 0px;
								border-bottom    : 1px solid var(menuBorderColor);
							} 
						");
					break;
			}
			return $this->css;
		}
		
		public function root( $url, $path='/' ) {
			if ($this->root == $this->current) { // FIXME: this looks like nonsense
				$this->current = $path;
			}
			$this->rooturl = $url;
			$this->root    = $path;
			return $this;
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
			if ( $this->stripeOptions ) {
				$this->stripe( $this->stripeOptions );
			}
			if ( $this->levelOptions ) {
				$this->levels( $this->levelOptions );
			}
			if ( $this->autoIDOptions ) {
				$this->autoID( $this->autoIDOptions );
			}
			return parent::toString( $indent, $current );
		}
		
		private function _makeURL( $path, $parent ) {
/*
			- ?bla=bla    -> parent url + arguments
			- #bla        -> parent url + fragment
			- /path        -> absolute path -> append to root url
			- http://.../ -> url
			  ftp://
			  mailto:
			  [a-z]+:
			- rest is a path -> append to parent path
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
					$item['tagName'] = $this->options['itemTag'];
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
									$parentNode = $this->items[$newparent]->appendChild( ar_html::tag( $this->options['listTag'] ) );
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
		
		public function fill() {
			$args = func_get_args();
			foreach( $args as $list) {
				if ( ($list instanceof ar_storeFind) || ($list instanceof ar_storeParents) ) {
					$list = $list->call( $this->template, array( 'current' => $current, 'root' => $root ) );
				}
				if ( is_array($list) ) {
					$this->_fillFromArray( $list, $this->current );
				}
			}
			$this->filled = true;
			return $this;
		}
		
		public function stripe( $options = array() ) {
			$options += array(
				'striping'         => ar::listPattern( 'menuFirst .*', '(menuOdd menuEven?)*', '.* menuLast' ),
				'stripingContinue' => false
			);
			if ( $options['striping'] ) {
				if ( $options['stripingContinue'] ) {
					$this->getElementsByTagName('li')->setAttribute('class', array(
						'menuStriping' => $options['striping']
					) );
				} else {
					$this->childNodes->setAttribute( 'class', array(
						'menuStriping' => $options['striping']
					) );
					$uls = $this->getElementsByTagName('ul');
					foreach( $uls as $ul ) {
						$ul->childNodes->setAttribute( 'class', array(
							'menuStriping' => $options['striping']
						) );
					}
				}
			}
			$this->stripeOptions = $options;
			return $this;
		}
		
		public function levels( $options = array() ) {
			$options += array(
				'maxDepth'   => 5, 
				'startLevel' => 0
			);
			
			// add level classes to the ul/li tags, level-0, level-1, etc.
			if ( $options['maxDepth'] == 0 ) {
				return $this;
			}
			if (!isset($options['rootNode'])) {
				$options['rootNode'] = $this;
			}
			if ( $options['rootNode'] instanceof ar_htmlElement ) {
				$options['rootNode'] = ar_html::nodes( $options['rootNode'] );
			}
			if ($options['rootNode'] instanceof ar_htmlNodes && count($options['rootNode']) ) {
				$options['rootNode']->setAttribute( 'class', array('menuLevels' => 'menuLevel-'.$options['startLevel']) );
				foreach( $options['rootNode'] as $element ) {
					$element->childNodes->setAttribute( 'class', array( 'menuLevels' => 'menuLevel-'.$options['startLevel'] ) );
					$this->levels( array( 
						'maxDepth' => $options['maxDepth'] - 1, 
						'startLevel' => $options['startLevel'] + 1, 
						'rootNode' => $element->li->ul 
					) );
				}
			}
			$this->levelOptions = $options;
			return $this;
		}
		
		public function autoID( $options = array() ) {
			$options += array(
				'rootID' => 'menu', 
				'rootNode' => $element
			);
			// create unique id's per list item
			if (!isset($options['rootNode']) ) {
				$options['rootNode'] = $this;
			}
			$element = $options['rootNode'];
			if (!$element->attributes['id']) {
				$element->setAttribute( 'id', $options['rootID'] . '-ul' );
			}
			$list    = $element->li;
			$counter = 0;
			foreach ($list as $li) {
				$id = $options['rootID'] . '-' . $counter++;
				if ( !$li->attributes['id'] ) {
					$li->setAttribute( 'id', $id );
				}
				$ul = $li->ul;
				if (count($ul)) {
					$this->autoID( array(
						'rootID' => $id, 
						'rootNode' => $ul[0] 
					) );
				}
			}
			$this->autoIDOptions = $options;
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
		
		public function configure( $options ) {
			if ($options['top']) {
				$this->root( ar('loader')->makeURL($options['top']), $options['top'] );
			} else {
				$options['top'] = $this->root;
			}
			if ($options['current']) {
				$this->current( $options['current'] );
			} else {
				$options['current'] = $this->current;
			}
			$this->options = array_merge( $this->options, $options );
			return $this->options;
		}
		
		public function bar( $options = array() ) {
			$options = $this->configure( $options );
			$this->fill( ar_html_menu::bar( $options ) );
			return $this;
		}
		
		public function tree( $options = array() ) {
			$options = $this->configure( $options );
			$this->fill( ar_html_menu::tree( $options ) );
			return $this;	
		}
		
		public function crumbs( $options = array() ) {
			$options = $this->configure( $options );
			$this->fill( ar_html_menu::crumbs( $options ) );
			return $this;	
		}
		
		public function sitemap( $options = array() ) {
			$options = $this->configure( $options );
			$this->fill( ar_html_menu::sitemap( $options ) );
			return $this;			
		}
		
	}

?>