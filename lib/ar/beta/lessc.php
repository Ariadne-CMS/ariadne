<?php

ar_pinp::allow('ar_beta_lessc');

class ar_beta_lessc_Client extends lessc {
	protected function tryImport($importPath, $parentBlock, $out) {
		if ($importPath[0] == "function" && $importPath[1] == "url") {
			$newImportPath = $this->flattenList($importPath[2]);
		}

		$str = $this->coerceString($newImportPath);
		if ($str === null) return false;

		$url = $this->compileValue($this->lib_e($str));

		if ( preg_match( '|^([a-z]+)://|i' , $url, $matches )) {
			// url style
			if($matches[1] != "file") {
				return parent::tryImport($importPath, $parentBlock, $out);
			}
		}
		// FIXME: add support for template support
		return array(false, "/* import disabled: no local file access*/");
	}
}

class ar_beta_lessc extends arBase {

	public static function compile($string) {
		$client = new ar_beta_lessc_Client();
		try {
			return $client->compile($string);
		} catch( Exception $e ) {
			return ar_error::raiseError($e->getMessage(), 501, $e);
		}
	}
}

?>