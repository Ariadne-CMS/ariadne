<?php
  require("./ariadne.inc");
  require($ariadne."/configs/ariadne.phtml");
  require($ariadne."/configs/store.phtml");
  require($ariadne."/stores/mysql.phtml");

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

    $split=strrpos($PATH_INFO, "/");
    $path=substr($PATH_INFO,0,$split+1);
    $filename=substr($PATH_INFO,$split+1);

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

      if (!$filename) {
	$obj=$store->call("Get.phtml","", $store->get($path));
	if ($obj[0]) {
		header("Location: $PHP_SELF".$obj[0]->data->name.".ax");
		exit;
	} else {
		include ("./notfound.html");
		exit;
	}
      }

      $ax_file=$store->files."temp/".$store->nextid("files");
      $export_path=$path;
      include($store->code."includes/export.phtml");
      if (!$error) {
	if (file_exists($ax_file)) {
		header("content-type: application/ariadne-export");
		readfile($ax_file);
		unlink($ax_file);
	} else
		$error="Error processing tempory file $ax_file";
      } 


      $store->close();
 }

if ($error) {
?>
<script language="javascript">
	alert('<?php echo $error; ?>');
</script>
<?php
}
?>