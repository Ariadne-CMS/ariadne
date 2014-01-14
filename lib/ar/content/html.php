<?php
	ar_pinp::allow( 'ar_content_html' );
	ar_pinp::allow( 'ar_content_htmlContent' );

	class ar_content_html extends arBase {

		public static $editMode       = false;
		public static $editPrefix     = 'editable_';
		public static $editTemplate   = 'user.edit.page.html';
		public static $editTarget     = '_top';		
		protected static $editCounter = 0;
		
		public static function configure( $option, $value ) {
			switch ($option) {
				case 'editMode' : 
					self::$editMode = $value;
				break;
				case 'editPrefix' :
					self::$editPrefix = $value;
				break;
				case 'editTarget' :
					self::$editTarget = $value;
				break;
				case 'editTemplate' :
					self::$editTemplate = $value;
				break;
			}
		}

		public function __set( $name, $value ) {
			self::configure( $name, $value );
		}
		
		public function __get( $name ) {
			if ( isset( self::${$name} ) ) {
				return self::${$name};
			}
		}
	
		public static function link($content, $attributes = array(), $path = null, $url = null) {
			if (self::$editMode) {
				$url = ar_loader::makeURL($path);
				$attributes[ 'href' ]   = $url . self::$editTemplate;
				$attributes[ 'target' ] = self::$editTarget;
			} else {
				if ( !isset($url) ) {
					$url = ar_loader::makeURL($path);
				}
				$attributes['href'] = $url;
			}
			return ar_html::tag( 'a', $attributes, $content );
		}
		
		public static function editableLink($content, $attributes = array(), $path = null, $url = null ) {
			if (self::$editMode) {
				$attributes['onClick']    = 'event.cancelBubble = true;';
				$attributes['onDblClick'] = "document.location=this.href; return false;";
			}
			return self::link( $content, $attributes, $path, $url);
		}
		
		public static function editableDiv( $content, $name, $editTitle = '' ) {
			if (self::$editMode) {
				$context = pobject::getContext();
				$me      = $context["arCurrentObject"];
				list( $id, $registerContent ) = self::registerEditable($name);
				return ar_html::nodes( $registerContent, ar_html::tag(
					'div', 
					array( 
						'id'      => $id,
						'class'   => 'editable',
						'ar:path' => $me->path,
						'ar:id'   => $me->id,
						'title'   => $editTitle
					), 
					self::parse( $content ) 
				) );
			} else {
				return self::parse( $content );
			}
		}
		
		public static function editableSpan( $content, $name, $editTitle = '' ) {
			if (self::$editMode) {
				$context = pobject::getContext();
				$me      = $context["arCurrentObject"];
				list( $id, $registerContent ) = self::registerEditable($name);
				return ar_html::nodes( $registerContent, ar_html::tag(
					'span', 
					array( 
						'id'      => $id,
						'class'   => 'editable',
						'ar:path' => $me->path,
						'ar:id'   => $me->id,
						'title'   => $editTitle
					), 
					self::parse( $content ) 
				) );
			} else {
				return self::parse( $content );
			}
		}
		
		public static function editableTextSpan( $content, $name, $editTitle = '' ) {
			if (self::$editMode) {
				$context  = pobject::getContext();
				$me       = $context["arCurrentObject"];
				$register = self::registerEditable($name);
				return ar_html::nodes( 
					$register['content'], 
					ar_html::tag(
						'span', 
						array( 
							'id'      => $register['id'],
							'class'   => array( 'editable', 'text-only' ),
							'ar:path' => $me->path,
							'ar:id'   => $me->id,
							'title'   => $editTitle
						), 
						self::parse( $content ) 
					)
				);
			} else {
				return self::parse( $content );
			}
		}

		public static function registerEditable( $name ) {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			$id      = self::$editCounter++;
			$prefix  = self::$editPrefix;
			return array(
				'id' => $prefix.$id,
				'content' => ar_html::tag(
					'script', 
					" parent.registerDataField('".$prefix.$id."', '".AddCSlashes($name, ARESCAPE)."', '".$me->path."', '".$me->id."' );"
				)
			);
		}
		
		public static function editablePage($page=null, $name=null, $editTitle='', $default = null, $nls = null) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if ( !isset($nls) ) {
				$nls = $me->nls;
			}
			if ( !isset($name) ) {
				$name = 'name';
				$editName = "[$nls][page]";
			} else if ($name[0] != '[') {
				$editName = "[$nls][$name]";
			} else {
				$editName = $name;
				$temp = explode( '[' , $name );
				$nls = substr($temp[0], 0, -1);
				$name = substr($temp[1], 0, -1);
			}
			if ( !isset($page) ) {
				$data = $me->data->{$nls};
				if ( isset($default) && self::isEmpty( $data->{$name} ) ) {
					$page = $default;
				} else {
					$page = $data->{$name};
				}
			}
			if (self::$editMode) {
				return self::editableDiv( self::parse($page), $name, $editTitle);
			} else {
				return self::parse( $page );
			}
		}
		
		public static function editableName( $content = null, $name = null, $editTitle = '', $default = null, $nls = null) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if ( !isset($nls) ) {
				$nls = $me->nls;
			}
			if ( !isset($name) ) {
				$name = 'name';
				$editName = "[$nls][name]";
			} else if ($name[0] != '[') {
				$editName = "[$nls][$name]";
			} else {
				$editName = $name;
				$temp = explode( '[' , $name );
				$nls = substr($temp[0], 0, -1);
				$name = substr($temp[1], 0, -1);
			}
			if ( !isset($content) ) {
				$data = $me->data->{$nls};
				if ( isset($default) && self::isEmpty( $data->{$name} ) ) {
					$content = $default;
				} else {
					$content = $data->{$name};
				}
			}
			if (self::$editMode) {
				return self::editableTextSpan( $content, $name, $editTitle);
			} else {
				return $content;
			}		
		}
		
		public static function getBody( $html ) {
			$html = preg_replace('|</BODY.*$|is', '', $html);
			$errno = preg_last_error();
			if( $html === null || $errno != PREG_NO_ERROR ){
				debug('preg_replace returned null errno '. $errno .' in ' . __CLASS__ . ':' . __FUNCTION__ . ':' . __LINE__ . '?');
				debug('preg error:'. self::pregError($errno));
				$html = '';
			}
			$html = preg_replace('/^.*<BODY[^>]*>/is', '', $html);
			$errno = preg_last_error();
			if( $html === null || $errno != PREG_NO_ERROR ){
				debug('preg_replace returned null, errno '. $errno .' in ' . __CLASS__ . ':' . __FUNCTION__ . ':' . __LINE__ . '?');
				debug('preg error:'. self::pregError($errno));
				$html = '';
			}
			return new ar_content_htmlContent( $html );
		}

		public static function parse( $html, $full = false ) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			include_once($me->store->get_config('code')."modules/mod_url.php");
			if (!$full) {
				$html = self::getBody( $html );
			}
			return new ar_content_htmlContent( URL::ARtoRAW( $html ) );
		}

		public static function isEmpty( $html ) {
			$html = self::getBody( $html );
			return trim( str_replace( '&nbsp;', ' ', 
				strip_tags( $html, '<img><object><embed><iframe>') ) )=='';
		}

		public static function clean( $html, $settings = null ) {
			global $AR, $ARCurrent;
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];

			if( !isset($settings) ) {
				if (!$ARCurrent->arEditorSettings) {
					$settings = $me->call("editor.ini");
				} else {
					$settings = $ARCurrent->arEditorSettings;
				}
			}

			if ($settings["htmlcleaner"]["enabled"] || $settings["htmlcleaner"]===true) {
				include_once($me->store->get_config("code")."modules/mod_htmlcleaner.php");
				$config	= $settings["htmlcleaner"];
				$html 	= htmlcleaner::cleanup($html, $config);
			}

			if ($settings["htmltidy"]["enabled"] || $settings["htmltidy"]===true) {
				include_once($me->store->get_config("code")."modules/mod_tidy.php");
				if ($settings["htmltidy"]===true) {
					$config	= array();
					$config["options"] = $AR->Tidy->options;
				} else {
					$config = $settings["htmltidy"];
				}
				$config["temp"]	= $me->store->get_config("files")."temp/";
				$config["path"]	= $AR->Tidy->path;
				$tidy			= new tidy($config);
				$result			= $tidy->clean($html);
				$html			= $result["html"];
			}

			if ($settings["allow_tags"]) {
				$html			= strip_tags($html, $settings["allow_tags"]);
			}
			$html = self::stripARNameSpace( $html );
			return new ar_content_htmlContent( $html );
		}

		public static function compile($html, $language='') {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			include_once($me->store->get_config('code')."modules/mod_url.php");
			include_once($me->store->get_config('code')."modules/mod_htmlparser.php");
			if (!$language) {
				$language = $me->nls;
			}
			$html = URL::RAWtoAR($html, $language);
			$newpage = $html;
			$nodes = htmlparser::parse($newpage, Array('noTagResolving' => true));
			// FIXME: the isChanged check is paranoia mode on. New code ahead.
			// will only use the new compile method when it is needed (htmlblocks)
			// otherwise just return the $html, so 99.9% of the sites don't walk
			// into bugs. 21-05-2007
			$isChanged = self::compileWorker($nodes);
			if ($isChanged) {
				return new ar_content_htmlContent( htmlparser::compile($nodes) );
			} else {
				return new ar_content_htmlContent( $html );
			}
		}

		private static function compileWorker(&$node) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$result = false;
			$contentEditable = "";
			if (isset($node['attribs']['contenteditable'])) {
				$contentEditable = "contenteditable";
			} else if (isset($node['attribs']['contentEditable'])) {
				$contentEditable = "contentEditable";
			}
			if ($contentEditable) {
				$node['attribs']['ar:editable'] = $node['attribs'][$contentEditable];
				unset($node['attribs'][$contentEditable]);
				$result = true;
			}
			if ($node['attribs']['ar:type'] == "template") {
					$path		= $me->make_path($node['attribs']['ar:path']);
					$template	= $node['attribs']['ar:name'];
					$argsarr	= Array();
					if (is_array($node['attribs'])) {
						foreach ($node['attribs'] as $key => $value) {
							if (substr($key, 0, strlen('arargs:')) == 'arargs:') {
								$name = substr($key, strlen('arargs:'));
								$argsarr[$name] = $name."=".$value;
							}
						}
					}
					$args = implode('&', $argsarr);

					$node['children'] = Array();
					$node['children'][] = Array(
						"type" => "text",
						"html" => "{arCall:$path$template?$args}"
					);
					// return from worker function
					return true;
			}
			if (is_array($node['children'])) {
				foreach ($node['children'] as $key => $child) {
					// single | makes the following line always run the compileworker
					// method, while any return true in that method makes $result true
					$result = $result | self::compileWorker($node['children'][$key]);
				}
			}
			return $result;
		}

		public static function getReferences($html) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			// Find out all references to other objects
			// (images, links) in this object, so we can
			// warn the user if he tries to delete/rename
			// an object which is still referenced somewhere
			// Use Perl compatible regex for non-greedy matching
			preg_match_all("/['\"](\{(arSite|arRoot|arBase|arCurrentPage)(\/[a-z][a-z])?}.*?)['\"]/", $html, $matches);
			$refs	= preg_replace(
				array(
					"|{arSite(/[a-z][a-z])?}|",
					"|{arRoot(/[a-z][a-z])?}|",
					"|{arBase(/[a-z][a-z])?}|", 
					"|{arCurrentPage(/[a-z][a-z])?}|" ),
				array(
					$me->currentsite(), 
					"", 
					"", 
					$me->path), 
				$matches[1]);
			foreach ($refs as $ref) {
				if (substr($ref, -1) != '/' && !$me->exists($ref)) {
					// Drop the template name
					$ref	= substr($ref, 0, strrpos($ref, "/")+1);
				}
				$result[]	= $ref;
			}
			return $result;
		}

		public static function stripARNameSpace($html) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			include_once($me->store->get_config('code')."modules/mod_htmlcleaner.php");
			$cleanAR = array(
				'rewrite' => array(
					'^(A|IMG|DIV)$' => array(
						'^ar:.*' => false,
						'^arargs:.*' => false,
						'^class' => Array(
							'htmlblock[ ]*uneditable[ ]*' => false
						)
					)
				),
				'delete_emptied' => Array(
					'div', 'a'
				)
			);
			return new ar_content_htmlContent( htmlcleaner::cleanup( $html, $cleanAR ) );
		}

		private static function pregError( $errno ) {
			switch($errno) {
				case PREG_NO_ERROR:
					$result = 'There is no error.';
					break;
				case PREG_INTERNAL_ERROR:
					$result = 'There is an internal error!';
					break;
				case PREG_BACKTRACK_LIMIT_ERROR:
					$result = 'Backtrack limit was exhausted!';
					break;
				case PREG_RECURSION_LIMIT_ERROR:
					$result = 'Recursion limit was exhausted!';
					break;
				case PREG_BAD_UTF8_ERROR:
					$result = 'Bad UTF8 error!';
					break;
				case PREG_BAD_UTF8_OFFSET_ERROR:
					$result = 'Bad UTF8 offset error!';
					break;
				default:
					$result = 'Unknown preg errno '.$errno;
			}
			return $result;
		}
	}
	
	class ar_content_htmlContent extends arBase {
	
		public $html = null;
		
		public function __construct( $html = null ) {
			$this->html = (string) $html;
		}
	
		public function __toString() {
			return $this->html;
		}

		public function clean( $settings = null ) {
			$this->html = (string) ar_content_html::clean( $this->html, $settings );
			return $this;
		}
		
		public function compile( $language = '' ) {
			$this->html = (string) ar_content_html::compile( $this->html, $language );
			return $this;
		}
		
		public function getBody() {
			return new ar_content_htmlContent( (string) ar_content_html::getBody( $this->html ) );
		}
		
		public function getReferences() {
			return ar_content_html::getReferences( $this->html );
		}
		
		public function isEmpty() {
			return ar_content_html::isEmpty( $this->html );
		}
		
		public function parse( $full = false ) {
			$this->html = (string) ar_content_html::parse( $this->html, $full );
			return $this;
		}
		
		public function stripARNameSpace() {
			$this->html = (string) ar_content_html::stripARNameSpace( $this->html );
			return $this;
		}
	}
?>