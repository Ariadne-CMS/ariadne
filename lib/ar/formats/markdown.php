<?php
	ar_pinp::allow( 'ar_formats_markdown');
	ar_pinp::allow( 'ar_formats_markdown_Parser', array(
		'compile', 'configure'
	) );

	class ar_formats_markdown extends arBase {
		static $options = array();
		public static function compile( $markdown ) {
			$parser = self::parser();
			return $parser->compile( $markdown );
		}

		public static function parser() {
			$parser = new ar_formats_markdown_Parser();
			$parser->configure(self::$options);
			return $parser;
		}
	}

	class ar_formats_markdown_Parser extends Parsedown {
		public function compile($markdown) {
			return $this->text($markdown);
		}
		public function _compile($markdown) {
			return $this->compile($markdown);
		}
		public function configure($option, $value=null) {
			if ( is_array($option) ) {
				foreach($option as $key => $value) {
					$this->configure($key, $value);
				}
			} else {
				switch ($option) {
					case 'LineBreaks':
						$this->setBreaksEnabled($value);
					break;
					case 'EscapeMarkup':
						$this->setMarkupEscaped($value);
					break;
				}
			}
			return $this;
		}
		public function _configure($option, $value=null) {
			return $this->configure($option, $value);
		}
	}
