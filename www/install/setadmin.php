<?php
  if ($password) {
    include("../ariadne.inc");
    include($ariadne."/configs/ariadne.phtml");
    include($ariadne."/configs/store.phtml");
    include_once($ariadne."/stores/".$store_config["dbms"]."store.phtml");
	include_once($ariadne."/includes/loader.web.php");

	$inst_store = $store_config["dbms"]."store";
    $store=new $inst_store(".",$store_config);
  
    $data=unserialize('O:6:"object":5:{s:4:"name";s:13:"Administrator";s:5:"login";s:5:"admin";s:8:"password";s:13:"0vZxQzc/c2glI";s:6:"groups";a:1:{s:21:"/system/groups/admin/";s:5:"admin";}s:8:"loggedon";i:949605712;}');
    $data->password=ARCrypt($password);
    $store->save("/system/users/admin/","puser",$data);
    $store->close();
    echo "You should now be able to log on";
  } else {
?>
<form>
admin password <input type="text" name="password">
</form>
<p>
Please remember to remove this script and the entire 'install' directory, or
anyone will be able to reinstall your database, which isn't a good thing.
<?php
  }
?>