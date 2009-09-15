<?php
  /******************************************************
  Ariadne tree widget, based on YAHOO User Interface library.

  start met $path geopend

  ******************************************************/

	/* retrieve HTTP GET variables */
	$path 	= $_GET["path"];
	$name 	= $_GET["name"];
	$icon	= $_GET["icon"];
	$loader = $_GET["loader"];	
	$wwwroot = $_GET["wwwroot"];
	$interface = $_GET["interface"];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Tree <?php echo $path; ?></title>
<link rel="stylesheet" type="text/css" href="http://developer.yahoo.com/yui/build/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="http://developer.yahoo.com/yui/build/treeview/assets/skins/sam/treeview.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $wwwroot;?>widgets/yui_tree/yui_tree.css" />
<script type="text/javascript" src="http://developer.yahoo.com/yui/build/yahoo/yahoo.js"></script>
<script type="text/javascript" src="http://developer.yahoo.com/yui/build/event/event.js"></script>
<script type="text/javascript" src="http://developer.yahoo.com/yui/build/connection/connection.js"></script>
<script type="text/javascript" src="http://developer.yahoo.com/yui/build/treeview/treeview.js"></script>

<script type="text/javascript" src="<?php echo $wwwroot; ?>widgets/yui_tree/yui_tree.js"></script>
<script type="text/javascript">
	// Pass the settings for the tree to javascript.
	muze.apps.ariadne.explore.tree.loaderUrl 	= '<?php echo $loader; ?>';
	muze.apps.ariadne.explore.tree.basePath 	= '<?php echo $path; ?>';
	muze.apps.ariadne.explore.tree.baseName		= '<?php echo $name; ?>';
	muze.apps.ariadne.explore.tree.baseIcon		= '<?php echo $icon; ?>';
</script>


</head>
<body class="yui-skin-sam">
	<div class="treelogo">
		<img src="<?php echo $wwwroot;?>images/tree/logo.gif">
	</div>
	<div id="treeDiv"></div>
	<div id="treecontrol">
		<a href="<?php echo $loader . $path; ?>logoff.phtml" title="Log off" target="_top"><img src="<?php echo $wwwroot;?>images/winxp/logoff.png" alt="Log off"></a>
		<a href="#" onclick="document.location.href=document.location.href; return false;" title="Refresh"><img src="<?php echo $wwwroot;?>images/winxp/refresh.png" alt="Refresh"></a>
	</div>
</body>
</html>