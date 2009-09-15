<?php
  // make sure this page is never cached, that creates problems with
  // logging in/out of ariadne.
  Header("Cache-control: no-cache");
  Header("Expires: ".GMDate("D, d M Y H:i:s")." GMT");
  // load /system/login.html which will do the job
  $_SERVER["PATH_INFO"]="/system/ariadne.html";
  include("./loader.php");
?>