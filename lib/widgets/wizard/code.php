<?php
  /******************************************************************
   code.php					   Muze Ariadne v2.0b
   ------------------------------------------------------------------

   No result.

  ******************************************************************/

  function wgWizKeepVars($array, $prefix="") {
    if (!$prefix) {
      $prefix="arStoreVars";
    } 
    $prefix.="[";
    $postfix="]";
    @reset($array);
    while (list($key, $value)=@each($array)) {
      if (is_array($value)) {
        if ($key!="arStoreVars") {
          wgWizKeepVars($value, $prefix.$key.$postfix);
        }
      } else {
        echo "<input type=\"hidden\" name=\"".$prefix.$key.$postfix."\" value=\"".ereg_replace('"','&quot;',$value)."\">\n";
      }
    }
  }

  function wgWizGetAction($wgWizButtonPressed) {
    global $ARnls;
    $arReverseControl[$ARnls["next"]]="next";
    $arReverseControl[$ARnls["prev"]]="prev";
    $arReverseControl[$ARnls["save"]]="save";
    $arReverseControl[$ARnls["back"]]="back";
    return $arReverseControl[$wgWizButtonPressed];
  }

?>