#!/usr/bin/php4 -q
<?php
  $ariadne="../";
  include($ariadne."configs/ariadne.phtml");
  include($ariadne."configs/store.phtml");
  include($ariadne."includes/loader.web.php");

  $languages=Array('nl','de','es','pl','hr','it','fr','sv', 'en');
  $modules=Array('','winxp.','ieedit.','menu.');
  $target='./new/';
  define('NLSESCAPE', str_replace("'", "", ARESCAPE));

  ob_start();


  reset($modules);
  while (list($key1, $module)=each($modules)) {
    include($module."en");  
    $default=$ARnls;
    ksort($default);
    unset($ARnls);
    reset($languages);
    while( list($key2, $language)=each($languages)) {
//      echo "Updating ".$module.$language."\n";
      include($module.$language);
      reset($default);



      echo "<?"."php\n\n";
      @include($module.$language.".head");
      while (list($arkey, $arvalue)=each($default)) {
        $tabs=substr("								",(int)((strlen($arkey)+2)/4));
        if ($ARnls[$arkey]) {
          echo "	\$ARnls[\"$arkey\"]$tabs=	\"".AddCSlashes(str_replace('\\\'', "'", $ARnls[$arkey]), NLSESCAPE)."\";\n";
        } else {
          echo "	\$ARnls[\"$arkey\"]$tabs=	\"!".AddCSlashes(str_replace('\\\'', "'", $arvalue), NLSESCAPE)."\";\n";
        }
      }
      unset($ARnls);
      echo "\n\n?".">";

      $file=ob_get_contents();
      ob_clean();

      $filename=$target.$module.$language;
      // write file
	  if ($fp=fopen($filename, 'w')) {
        fwrite($fp, $file);
        fclose($fp);
      } else {
        die("ERROR: couldn't open $filename for writing.\n");
      }

    }
    unset($default);
  }

  ob_end_clean();


?>