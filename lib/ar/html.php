<?php
	class ar_html extends arBase {
//		protected $_pinp_export = array(
//			'name', 'value', 'attribute', 'attributes', 'tag', 'nodes', 'form'
//		);

		public static function name($name) {
			return preg_replace('/[^a-z0-9:]*/', '', strtolower($name));
		}

		public static function value($value) {
			if (is_array($value)) {
				$content = '';
				foreach($value as $subvalue) {
					$content .= ' '.self::value($subvalue);
				}
				$content = substr($content, 1);
			} else if (is_bool($value)) {
				$content = $value ? 'true' : 'false';
			} else {
				$content = htmlspecialchars($value);
			}
			return $content;
		}
		
		public static function attribute($name, $value) {
			if (is_numeric($name)) {					
				return ' '.self::name($value);
			} else {
				return ' '.self::name($name).'="'.self::value($value).'"';
			}
		}
		
		public static function attributes($attributes) {
			$content = '';
			if (is_array($attributes)) {
				foreach($attributes as $key => $value) {
					$content .= self::attribute($key, $value);
				}
			}
			return $content;
		}

		private static function _mustClose($name) {
			return in_array($name, array( 'script', 'div' ) );
		}
		
		public static function tag() {
			$args = func_get_args();
			$name = $args[0];
			if (isset($args[1])) {
				if (is_array($args[1]) && !is_a($args[1], 'htmlNodes')) { //attributes
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
			$name = self::name($name);
			if ((isset($content) && $content!=='') || self::_mustClose($name)) {
				return '<'.$name.self::attributes($attributes).'>'.self::indent($content).'</'.$name.'>'."\n";
			} else {
				return '<'.$name.self::attributes($attributes).' />'."\n";
			}
		}
		
		private static function indent($content) { // disable this for production code
			if (strpos($content, '<')!==false) {
				return "\n".preg_replace("|<([^/])|","\t<$1",$content);
			} else {
				return $content;
			}
		}
		
		public static function nodes() {
			$args = func_get_args();
			$nodes = call_user_func_array(array('ar_htmlNodes', 'mergeArguments'), $args);
			return new ar_htmlNodes($nodes);
		}

		public static function form($fields, $buttons=null, $action=null, $method='POST') {
			return new ar_html_form($fields, $buttons, $action, $method);
		}
	}

	class ar_htmlNodes extends ArrayObject {
		public static function mergeArguments(){
			$args = func_get_args();
			$nodes = array();
			foreach ($args as $input) {
				if (is_array($input) || is_a($input, 'ar_htmlNodes')) {
					$nodes = array_merge($nodes, (array)$input);
				} else {
					$nodes[] = $input;
				}
			}
			return $nodes;
		}
		public function __construct() {
			$args = func_get_args();
			$nodes = call_user_func_array(array('ar_htmlNodes', 'mergeArguments'), $args);
			parent::__construct($nodes);
		}
		public function __toString() {
			return join('', (array)$this);
		}
	}
	
?>