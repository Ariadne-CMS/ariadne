<?php
  /******************************************************************
   code.php					   Muze Ariadne v2.0b
   ------------------------------------------------------------------

   No result.

  ******************************************************************/
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
    if (!($regexp=$ARCurrent->regexp)) {
      $regexp='^arStoreVars\[(';
      reset($AR->nls->list);
      while (list($key, $value)=each($AR->nls->list)) {
        $regexp.=$key.'|';
      }
      $regexp=substr($regexp,0,-1).')\]\[$';
      $ARCurrent->regexp=$regexp;
    }
    if (ereg($ARCurrent->regexp, $prefix)) {
      $toplevel=true;
    }
    $postfix="]";
    @reset($array);
    while (list($key, $value)=@each($array)) {
      if (is_array($value)) {
        if ($key!=="arStoreVars") {
          wgWizKeepVars($value, $prefix.$key.$postfix);
        }
      } else if ($ARCurrent->override) { // don't check $ARCurrent->seenit, do set it.
        if ($toplevel) { // this is a normal name-value pair
          $ARCurrent->seenit[$prefix.$key.$postfix]=true;
        } else { // it's part of an array
          $ARCurrent->seenit[$prefix]=true;
        }
        echo "<input type=\"hidden\" name=\"".$prefix.$key.$postfix."\" value=\"".str_replace('"','&quot;',$value)."\">\n";
      } else if (!$ARCurrent->seenit[$prefix] && !$toplevel) { // value part of array
        echo "<input type=\"hidden\" name=\"".$prefix.$key.$postfix."\" value=\"".str_replace('"','&quot;',$value)."\">\n";
      } else if (!$ARCurrent->seenit[$prefix.$key.$postfix] && $toplevel) { // value not in array
        echo "<input type=\"hidden\" name=\"".$prefix.$key.$postfix."\" value=\"".str_replace('"','&quot;',$value)."\">\n";
      }
    }
  }

  function wgWizGetAction($wgWizButtonPressed) {
    global $ARnls;
    $arReverseControl[$ARnls["next"]." >"]="next";
    $arReverseControl["< ".$ARnls["prev"]]="prev";
    $arReverseControl[$ARnls["save"]]="save";
    $arReverseControl[$ARnls["back"]]="back";
    return $arReverseControl[$wgWizButtonPressed];
  }

  // code for pinp: calculate and return (preliminary) wgWizNextStep
  if (!$wgWizControl) {
    $wgWizControl=$this->getdata("wgWizControl","none");
  }
  if ($wgWizControl) {
    $wgResult=wgWizGetAction($wgWizControl);
  }

?>