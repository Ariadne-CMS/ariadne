<?php
  require("./ariadne.inc");
  require($ariadne."/configs/ariadne.phtml");
  require($ariadne."/configs/store.phtml");
  include_once($ariadne."/stores/mysqlstore.phtml");
  include_once($ariadne."/modules/mod_session.phtml");

  function ldSetRoot($session='', $nls='') {
	global $store, $AR, $ARCurrent;
	$root=$AR->root;
	if ($session) {
		$root.="/=$session=";
		$ARCurrent->session->id=$session;
	}
	if ($nls) {
		$root.="/$nls";
		$ARCurrent->nls=$nls;
	}
	if ($store) { // loader.php uses this function before the store is initialized.
		$store->root=$root;
	}	
	return $root;
  }

  function ldSetNls($nls) {
      global $ARCurrent;
      $session=$ARCurrent->session->id;
      return ldSetRoot($session, $nls);
  }

  function ldSetSession($session='') {
    global $ARCurrent;
    $nls=$ARCurrent->nls;
    return ldSetRoot($session, $nls);
  }
 
  function ldStartSession($sessionid='') {
	global $ARCurrent, $ariadne;
    require($ariadne."/configs/sessions.phtml");
	$ARCurrent->session=new session($session_config,$sessionid);
    return ldSetSession($ARCurrent->session->id);
  }

  function squisharray($name, $array) {
    while (list($key, $val)=each($array)) {
      if (is_array($val)) {
        $result.=squisharray($name."[".$key."]",$val);
      } else {
        $result.="&".$name."[".RawUrlEncode($key)."]=".RawUrlEncode($val);
      }
    }
    return $result;    
  }

  if (!$PATH_INFO) {

    Header("Location: $PHP_SELF/");
    exit;

  } else {


    // go check for a sessionid
    $root=$AR->root;
    $re="^/=(.*)=/";
    if (eregi($re,$PATH_INFO,$matches)) {
		$session_id=$matches[1];
		$PATH_INFO=substr($PATH_INFO,strlen($matches[0])-1);
		$root=ldStartSession($session_id);
    }

    $AR->login="public";
    $split=strrpos($PATH_INFO, "/");
    $path=substr($PATH_INFO,0,$split+1);
    $function=substr($PATH_INFO,$split+1);
    if (!$function) {
      $function="view.html";
    }
    $split=strpos(substr($PATH_INFO, 1), "/");
    $ARCurrent->nls=substr($path, 1, $split);
    if (!$AR->nls->list[$ARCurrent->nls]) {
      // not a valid language
      $ARCurrent->nls="";
      $nls=$AR->nls->default;
      $cachenls="";
    } else {
      // valid language
      $path=substr($path, $split+1);
      $root=ldSetNls($ARCurrent->nls);
      $nls=$ARCurrent->nls;
      $cachenls="/$nls";
    }

    require($ariadne."/nls/".$nls);
    if (substr($function, -6)==".phtml") {
      // system template: no language check
      $ARCurrent->nolangcheck=1;
    }
    $cachedimage=$store_config["files"]."cache".$cachenls.$path.$function."?".$QUERY_STRING;
    $cachedheader=$store_config["files"]."cacheheaders".$cachenls.$path.$function."?".$QUERY_STRING;
    // yes, the extra '?' is needed, don't remove it. trust me.
    
    $timecheck=time();
    if (file_exists($cachedimage) && 
       (strpos(implode("",getallheaders()),"no-cache") === false) &&
       (($mtime=filemtime($cachedimage))>$timecheck) &&
       ($REQUEST_METHOD!="POST")) {
        // now send caching headers too, maximum 1 hour client cache.
        // FIXME: make this configurable. per directory? as a fraction?
        $freshness=$mtime-$timecheck;
		if ($freshness>3600) { 
			$cachetime=$timecheck+3600;
			$cacheseconds=3600;
		} else {
			$cachetime=$mtime; 
			$cacheseconds=$freshness;
		}
		// now send client side cache headers
        // e.g. Expires: Thu, 01 Dec 1994 16:00:00 GMT
        Header("Expires: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$cachetime));
		Header("Cache-Control: must-revalidate, max-age=$cacheseconds, s-max-age=$cacheseconds");
		if (file_exists($cachedheader)) {
			$headers=file($cachedheader);
			while (list($key, $header)=@each($headers)) {
				Header(chop($header));
			}
		} 
		readfile($cachedimage);
    } else {      
      $store=new mysqlstore($root,$store_config);

      $args=$QUERY_STRING;
      if ($REQUEST_METHOD=="POST") {
        $nocache=1; // never cache pages resulting from 'post' operations.
        while ( list( $key, $val ) = each( $HTTP_POST_VARS ) ) {
          if (is_array($val)) {
            $args.=squisharray($key, $val);
          } else { 
            $args.="&".RawUrlEncode($key)."=".RawUrlEncode($val);
          }
        }
      }
      $store->call($function, $args, $store->get($path));
      if (!$store->total) {
        $requestedpath=$path;
        while (!$store->exists($path)) {
          $path=$store->make_path($path, "..");
        }
        $store->call("user.notfound.html",
                     "arRequestedPath=".RawUrlEncode($requestedpath).
                     "arRequestedTemplate=".RawUrlEncode($function),
                     $store->get($path));
      }
      $store->close();

    }
    if ($ARCurrent->session) {
      $ARCurrent->session->save();
    }
  }
?>