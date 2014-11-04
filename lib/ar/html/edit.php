<?php
	ar_pinp::allow( 'ar_html_edit' );

	class ar_html_edit extends arBase {

		static $enabled = false;

		public static function enabled() {
			return self::$enabled;
		}

		public static function enable() {
			self::$enabled = true;
		}

		public static function disable() {
			self::$enabled = false;
		}

		public static function el() {
			$args = func_get_args();
			$el = call_user_func_array( 'ar_html::el', $args );
			if ( self::$enabled ) {
				switch ( $el->tagName ) {
					case 'input' :
					case 'select' :
						$html = false;
					break;
					default :
						$html = ( isset( $el->attributes['data-ar-html'] ) ? $el->attributes['data-ar-html'] : true );
					break;
				}
				if ( !$el->attributes['data-ar-path'] ) {
					$el->setAttribute( 'data-ar-path', ar::context()->getPath() );
				}
				$el->setAttributes( array(
					'class' => array('ar_edit' => 'ar-editable'),
					'data-ar-html' => $html
				) );
			} else {
				// remove data-* attributes
				$el->removeAttribute('data-ar-path');
				$el->removeAttribute('data-ar-name');
				$el->removeAttribute('data-ar-html');
			}
			return $el;
		}

		public static function info( $path = null ) {
			if ( !isset($path) ) {
				$path = ar::context()->getPath();
			}
			if ( self::$enabled ) {
				return ' data-ar-path="'.htmlspecialchars( $path ).'"';
			} else {
				return '';
			}
		}

	}
