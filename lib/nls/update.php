#!/usr/bin/php4 -q
<?php
  include("../configs/ariadne.phtml");
  include("../includes/loader.cmd.php");
  include("en");  

  $default=$ARnls;
  ksort($default);
  unset($ARnls);

  reset($AR->nls->list);
  while( list($key, $value)=each($AR->nls->list)) {
    include($key);
	reset($default);
    while (list($arkey, $arvalue)=each($default)) {
      $tabs=substr("								",(int)((strlen($arkey)+2)/4));
      if ($ARnls[$arkey]) {
        echo "	\$ARnls[\"$arkey\"]$tabs=	\"".AddCSlashes($ARnls[$arkey], ARESCAPE)."\";\n";
      } else {
        echo "	\$ARnls[\"$arkey\"]$tabs=	\"!".AddCSlashes($arvalue, ARESCAPE)."\";\n";
      }
    }
    unset($ARnls);
	echo "\n\n";
  }

  unset($default);
  include("menu.en");

  $default=$ARnls;
  ksort($default);
  unset($ARnls);

  reset($AR->nls->list);
  while( list($key, $value)=each($AR->nls->list)) {
    include("menu.".$key);
	reset($default);
    while (list($arkey, $arvalue)=each($default)) {
      $tabs=substr("								",(int)((strlen($arkey)+2)/4));
      if ($ARnls[$arkey]) {
        echo "	\$ARnls[\"$arkey\"]$tabs=	\"".AddCSlashes($ARnls[$arkey], ARESCAPE)."\";\n";
      } else {
        echo "	\$ARnls[\"$arkey\"]$tabs=	\"!".AddCSlashes($arvalue, ARESCAPE)."\";\n";
      }
    }
    unset($ARnls);
	echo "\n\n";
  }
  

?> 