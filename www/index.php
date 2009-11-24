<?php
  // make sure this page is never cached, that creates problems with
  // logging in/out of ariadne.
  Header("Cache-control: no-cache");
  Header("Expires: ".GMDate("D, d M Y H:i:s")." GMT");
  // load /system/login.html which will do the job
  $AR_PATH_INFO="/system/ariadne.html";
  $HTTP_SERVER_VARS["PATH_INFO"]="/system/ariadne.html"; // backwards compatible for old loaders, just to be sure
  include("./loader.php");
?>