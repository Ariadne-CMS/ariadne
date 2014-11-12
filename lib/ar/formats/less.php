<?php
	ar_pinp::allow( 'ar_formats_less');
	ar_pinp::allow( 'ar_formats_less_Parser', array( 
		'compile', 'setVariables', 'getCSS' 
	) );

	class ar_formats_less extends arBase {

		public static function compile( $less, $vars = null) {
			$parser = self::parser();
			if ( $vars ) {
				$parser->setVariables($vars);
			}
			$parser->parseString($less);
			return $parser->getCSS();
		}

		public static function parser() {
			$parser = new ar_formats_less_Parser(array(), null, array(
				new ILess_Importer_Callback( 
					/* import */
					function($path, $currentFileInfo) {
						ob_start();
						ar::call($path);
						$result = ob_get_contents();
						ob_end_clean();
						return new ILess_ImportedFile($path, $result, time() );
					},
					/* getLastModified */
					function($path, $currentFileInfo) {
						return time();
					}
				)
			));
			return $parser;
		}			
	}

	class ar_formats_less_Parser extends ILess_Parser {
		public function compile($less) {
			return $this->parseString($less);
		}
		public function _compile($less) {
			return $this->compile($less);
		}
		public function _setVariables($vars) {
			return $this->setVariables($vars);
		}
		public function _getCSS() {
			try {
				return $this->getCSS();
			} catch( \Exception $e ) {
				$err = ar_error::raiseError($e->getMessage(), $e->getCode(), $e );
				return $err;
			}
		}
		public function __toString() {
			try {
				return ''.$this->_getCSS();
			} catch( \Exception $e) {
				return ''.$e;
			}
		}
	}