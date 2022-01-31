<?php
  /******************************************************************
   code.php					   Muze Ariadne v2.1
   ------------------------------------------------------------------

   No result.

  ******************************************************************/
global $ARnls;
$ARnls->load('ariadne', $this->reqnls);

if( !function_exists("wgWizKeepVars") ) {
	function wgWizKeepVars($array, $prefix="") {
		// this function translates the given array to a list
		// of hidden input types. When $ARCurrent->override is on
		// there is no check to see wether a given variable
		// has already been seen and $ARCurrent->seenit is set.
		// In the case of an array, seenit is set to the name of
		// the array (for each element). In the case of a normal
		// variable, seenit is set to that variable's name.
		// When $ARCurrent->override is not set, each variable is
		// first checked against the 'seenit' array. If it is an
		// element of an array, seenit is checked against the name
		// of the array, else it is checked against the name of
		// the variable itself.
		// Only 'leaf' node or array elements are checked.

		global $ARCurrent, $AR;
		if (!$prefix) {
			$prefix="arStoreVars";
		}
		$prefix.="[";
		if ($prefix==="arStoreVars[") {
			$toplevel=true;
		}
		if (!($regexp=(isset($ARCurrent->regexp) ? $ARCurrent->regexp : ''))) {
			$regexp='/^arStoreVars\[(';
			reset($AR->nls->list);
			foreach( $AR->nls->list as $key => $value ) {
				$regexp.=$key.'|';
			}
			$regexp=substr($regexp,0,-1).')\]\[$/';
			$ARCurrent->regexp=$regexp;
		}
		if (preg_match($ARCurrent->regexp, $prefix)) {
			$toplevel=true;
		}
		$postfix="]";

		$ignoreVars = array(
			"wgWizAction" => true,
		);

		if( is_array($array ) ) {
			reset($array);
			foreach( $array as $key => $value ) {
				if( !isset($ignoreVars[$key]) || !$ignoreVars[$key] ) {
					if (is_array($value)) {
						if ($key!=="arStoreVars") {
							wgWizKeepVars($value, $prefix.$key.$postfix);
						}
					} elseif ($ARCurrent->override) { // don't check $ARCurrent->seenit, do set it.
						if ($toplevel) { // this is a normal name-value pair
							$ARCurrent->seenit[$prefix.$key.$postfix]=true;
						} else { // it's part of an array
							$ARCurrent->seenit[$prefix]=true;
						}
						$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
						echo "<input type=\"hidden\" name=\"".$prefix.$key.$postfix."\" value=\"".$value."\">\n";
					} elseif ((!isset($ARCurrent->seenit) || !isset($ARCurrent->seenit[$prefix]) || !$ARCurrent->seenit[$prefix]) && !$toplevel) { // value part of array
						$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
						echo "<input type=\"hidden\" name=\"".$prefix.$key.$postfix."\" value=\"".$value."\">\n";
					} elseif ((!isset($ARCurrent->seenit) || !isset($ARCurrent->seenit[$prefix.$key.$postfix]) || !$ARCurrent->seenit[$prefix.$key.$postfix]) && $toplevel) { // value not in array
						$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
						echo "<input type=\"hidden\" name=\"".$prefix.$key.$postfix."\" value=\"".$value."\">\n";
					}
				}
			}
		}
	}
}

if( !class_exists('pinp_wizard', false) ) {
	class pinp_wizard {
		function _wgWizKeepVars($array, $prefix="") {
			return wgWizKeepVars($array, $prefix);
		}
	}
}

if( !function_exists("wgWizGetAction") ) {
	function wgWizGetAction($wgWizButtonPressed) {
		global $ARnls;
		$arReverseControl[$ARnls["next"]." >"]="next";
		$arReverseControl["< ".$ARnls["prev"]]="prev";
		$arReverseControl[$ARnls["save"]]="save";
		$arReverseControl[$ARnls["back"]]="back";
		$arReverseControl[$ARnls["cancel"]]="cancel";
		return $arReverseControl[$wgWizButtonPressed];
	}
}
	// code for pinp: calculate and return (preliminary) wgWizNextStep
	if (!isset($wgWizControl) || !$wgWizControl) {
		$wgWizControl=$this->getdata("wgWizControl","none");
	}
	if ($wgWizControl) {
		$wgResult=wgWizGetAction($wgWizControl);
	}
?>
