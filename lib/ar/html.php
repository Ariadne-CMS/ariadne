<?php
	ar_pinp::allow('ar_html', array(
			'name', 'value', 'attribute', 'attributes', 'tag', 'nodes', 'form'
	));

	class ar_html extends ar_xml {

		private static $xhtml = false;

		public static function configure($option, $value) {
			switch ($option) {
				case 'xhtml' : 
					self::$xhtml = (bool)$value;
					break;
				default:
					parent::configure($option, $value);
					break;
			}
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
			if (!self::$xhtml || (isset($content) && $content!=='') || self::_mustClose($name)) {
				return '<'.$name.self::attributes($attributes).'>'.self::indent($content).'</'.$name.'>'."\n";
			} else {
				return '<'.$name.self::attributes($attributes).' />'."\n";
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

	class ar_htmlNodes extends ar_xmlNodes {
	}
	
?>