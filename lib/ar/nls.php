<?php

ar_pinp::allow( 'ar_nls' );
ar_pinp::allow( 'ar_nlsDictionary', array("load", "setLanguage", "getLanguage") );

class ar_nls extends arBase {

	public static function dict($defaultLanguage, $currentLanguage = null, $defaultVarName = "ARnls" ) {
		global $store; // FIXME!!
		$baseDir = $store->get_config("code")."nls/";
		return new ar_nlsDictionary($baseDir, $defaultLanguage, $currentLanguage, $defaultVarName );
	}

}

class ar_nlsDictionary extends arBase implements ArrayAccess {

	private $currentList = null;
	private $defaultList = null;
	private $baseDir = null;
	private $languages = array();
	private $loaded = array();
	public $currentLanguage = null;
	public $defaultLanguage = null;

	public function __construct( $baseDir, $defaultLanguage, $currentLanguage = null, $defaultVarName = "ARnls" ) {
	
		$this->baseDir = $baseDir;
		$this->defaultVarName = $defaultVarName;
	
		if( !isset($currentLanguage) ) {
			$currentLanguage = $defaultLanguage;
		}
		$this->languages[$defaultLanguage] = array();
		$this->languages[$currentLanguage] = array();
		
		$this->defaultList = &$this->languages[$defaultLanguage];
		$this->currentList = &$this->languages[$currentLanguage];
		
		$this->currentLanguage = $currentLanguage;
		$this->defaultLanguage = $defaultLanguage;

		$this->load();
	}

	public function setLanguage( $language ) {
		if( !isset( $this->languages[$language] ) ) {
			$this->languages[$language] = array();
		}
		$this->currentList = &$this->languages[$language];
		$this->currentLanguage = $language;
		$this->load();
		
		foreach( $this->loaded as $section => $nlslist ) {
			if( !isset($nlslist[$language]) ) {
				$this->load($section);
			}
		}
		return $this;
	}
	
	public function getLanguage( $language = null ) {
		return isset($language) ? $this->languages[$language] : $this->currentList;
	}
	
	public function load( $section = "", $nls = null, $varName = null ) {
		if( !isset($nls) ) {
			$nls = $this->currentLanguage;
		}
		if( !isset($varName) ) {
			$varName = $this->defaultVarName;
		}
		
		$nls = eregi_replace('[^a-z]*','',$nls);
		$re = '|[^a-z-_.:0-9/]*|i';				// only allow 'normal' characters
		$section = str_replace('//', '/', 			// protects against urls in the form of //google.com
				str_replace('..', '', 			// protects against ../../../../../etc/passwd
				preg_replace($re, '', $section))); // add .js if not set, remove .. and other dirty characters

		if( !$section ) {
			if( ($fullFile = $this->baseDir.$nls) && file_exists($fullFile) ) {
				include($fullFile);
				$this->languages[$nls] = array_merge($this->languages[$nls], (array)$$varName);
			}
		} else { 
			$this->loaded[$section][$nls] = true;
			$fullFile = $this->baseDir.$section.".".$nls;
			if( file_exists($fullFile) ) {
				include($fullFile);
				$this->languages[$nls] = array_merge($this->languages[$nls], (array)$$varName);
			} else {
				// FIXME: UGLY hack stolen from pobject::loadtext()
				global $ARCurrent;
				$context = pobject::getContext();
				$me = $context["arCurrentObject"];
				$arResult = $ARCurrent->arResult;
				$me->pushContext(Array());
					$oldnls = $me->reqnls;
					$me->reqnls = $nls;
					$oldAllnls = $ARCurrent->allnls;
					$ARCurrent->allnls = true;
					$me->CheckConfig($section, array("nls" => $nls));
					$ARCurrnet->allnls = $oldAllnls;
					$me->reqnls = $oldnls;
				$me->popContext();
				
				$nlsarray = array();
				if( is_array($ARCurrent->arResult) ) {
					$nlsarray = $ARCurrent->arResult;
				} elseif( is_array($me->{$varName}) ) {
					$nlsarray = $me->{$varName};
				} elseif( is_array($ARCurrent->{$varName}) ) {
					$nlsarray = $ARCurrent->{$varName};
				}
				$ARCurrent->arResult = $arResult;
				$this->languages[$nls] = array_merge($this->languages[$nls], $nlsarray);
			}
		}
		return $this;
	}

	public function offsetSet( $offset, $value ) {
		if ($offset == "") {
			$this->currentList[] = $value;
		} else {
			$this->currentList[$offset] = $value;
		}
	}

	public function offsetExists( $offset ) {
		return ( $this->offsetGet( $offset ) !== null );
	}

	public function offsetUnset( $offset ) {
		unset($this->currentList[$offset]);
	}

	public function offsetGet( $offset ) {
		if( isset( $this->currentList[$offset] ) ) {
			return $this->currentList[$offset];
		} elseif( strpos($offset, ":") !== false ) { // $ARnls["ariadne:foo"] => try and autoload "ariadne.$currentLanguage"
			list($section, $rest) = explode(":", $offset, 2);
			$this->load($section, $this->currentLanguage);
			if( isset( $this->currentList[$offset] ) ) {
				return $this->currentList[$offset];
			}
		}
		if( isset( $this->defaultList[$offset] ) ) {
			return $this->defaultList[$offset];
		} elseif( strpos($offset, ":") !== false ) { // $ARnls["ariadne:foo"] => try and autoload "ariadne.$defaultLanguage"
			list($section, $rest) = explode(":", $offset, 2);
			$this->load($section, $this->defaultLanguage);
			if( isset( $this->defaultList[$offset] ) ) {
				return $this->defaultList[$offset];
			}
		}
		return null;
	}
}

?>