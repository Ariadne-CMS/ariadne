#!/usr/local/bin/php -q
<pre>
<?php
  require("../ariadne.inc");
  require($ariadne."/configs/ariadne.phtml");
  require($ariadne."/configs/store.phtml");
  include_once($ariadne."/stores/mysql_install.phtml");
  $ERRMODE="text";

  $store=new mysqlstore_install(".",$store_config);
  
  echo "== creating main Ariadne Object Store\n\n";

  if ($store->initialize()) {

    $store->add_type("pshortcut","pobject");
    $store->add_type("pshortcut","pshortcut");
    $store->add_type("puser","pobject");
    $store->add_type("puser","pdir");
    $store->add_type("puser","puser");
    $store->add_type("pgroup","pobject");
    $store->add_type("pgroup","pdir");
    $store->add_type("pgroup","pgroup");
    $store->add_type("pfile","pobject");
    $store->add_type("pfile","pfile");
    $store->add_type("ppage","pobject");
    $store->add_type("ppage","ppage");
    $store->add_type("psite","pobject");
    $store->add_type("psite","pdir");
    $store->add_type("psite","psite");
    $store->add_type("pphoto","pobject");
    $store->add_type("pphoto","pphoto");
    $store->add_type("pphoto","pfile");
    $store->add_type("pphoto","ppage");
    $store->add_type("pphotobook","pobject");
    $store->add_type("pphotobook","pphotobook");
    $store->add_type("pphotobook","pdir");
    $store->add_type("pphotobook","pphoto");

    $name["value"]["string"]=50;
    $store->create_property("name", $name);

    $value["value"]["string"]=128;
    $store->create_property("value",$value);

    $ptext["value"]["string"]=128;
    $store->create_property("text",$ptext);

    $locked["id"]["string"]=16;
    $locked["duration"]["number"]=1;
    $store->create_property("locked", $locked);

    $login["value"]["string"]=16;
    $store->create_property("login", $login);

    $members["login"]["string"]=16;
    $store->create_property("members", $members);

    // now install all pcalendar and pcalitem objects.

    $timeframe["start"]["number"]=1;
    $timeframe["end"]["number"]=1;
    $store->create_property("timeframe", $timeframe);

    $priority["value"]["number"]=1;
    $store->create_property("priority", $priority);

    $store->add_type("pcalitem","pobject");
    $store->add_type("pcalitem","pcalitem");
    $store->add_type("pcalendar","pobject");
    $store->add_type("pcalendar","pdir");
    $store->add_type("pcalendar","pcalendar");

    // newspaper types and default objects

    $article["start"]["number"]=1;
    $article["end"]["number"]=1;
    $article["display"]["string"]=50;
    $store->create_property("article", $article);

    $published["value"]["number"]=1;
    $store->create_property("published",$published);

    $store->add_type("pscenario","pobject");
    $store->add_type("pscenario","pscenario");
    $store->add_type("particle","pobject");
    $store->add_type("particle","ppage");
    $store->add_type("particle","particle");
    $store->add_type("pnewspaper","pobject");
    $store->add_type("pnewspaper","pdir");
    $store->add_type("pnewspaper","pnewspaper");

    // Addressbook types and default objects

    $store->add_type("paddressbook","pobject");
    $store->add_type("paddressbook","pdir");
    $store->add_type("paddressbook","paddressbook");
    $store->add_type("pperson","pobject");
    $store->add_type("pperson","address");
    $store->add_type("pperson","pperson");
    $store->add_type("porganization","pobject");
    $store->add_type("porganization","address");
    $store->add_type("porganization","porganization");

    $address["street"]["string"]=50;
    $address["zipcode"]["string"]=6;
    $address["city"]["string"]=50;
    $address["country"]["string"]=50;
    $store->create_property("address", $address);

    echo "== importing ariadne.ax file\n\n";
    $import_path="/"; $ax_file="ariadne.ax";
  //  printf("install: ".$AR->ax->cmd_untar."\n", $ax_file, "");
    include($ariadne."/includes/import.phtml");

    if ($error) {
      error($error);
    }
  } else {
	error("store not initialized.");
  }
  $store->close(); 

  // session store

  require($ariadne."/configs/sessions.phtml");

  $sessionstore=new mysqlstore_install(".",$session_config);
  
  echo "== creating Ariadne Session Store\n\n";

  if ($sessionstore->initialize()) {

    $sessionstore->add_type("psession","pobject");
    $sessionstore->add_type("psession","psession");

  } else {
	error("store not initialized.");
  }
  $sessionstore->close();
  
?>
</pre>