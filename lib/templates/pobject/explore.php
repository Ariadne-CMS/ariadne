<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		//$base_object = $AR->user;
		$base_object = $this;

		if (!$ARCurrent->arTypeTree) {
			$base_object->call("typetree.ini");
		}

		// This set initializes the tree from the user object
		$path 	= $base_object->path;
		$name 	= $base_object->nlsdata->name;
		$icon 	= $ARCurrent->arTypeIcons[$base_object->type]['medium'] ? $ARCurrent->arTypeIcons[$base_object->type]['medium'] : $base_object->call('system.get.icon.php', array('size' => 'medium'));

		$loader = $this->store->get_config('root');
		$wwwroot = $AR->dir->www;
		$interface = $data->interface;

		$yui_base = $wwwroot . "js/yui/";

		$viewmodes = array( "list" => 1, "details" => 1, "icons" => 1);
		$viewmode = $_COOKIE["viewmode"];
		if( !$viewmode || !$viewmodes[$viewmode] ) {
			$viewmode = 'list';
		}
		$objectName = $nlsdata->name;
		if (!$objectName) {
			$objectName = $data->name;
		}

		$loadJS = array(
			'muze',
			'muze.event',
			'muze.dialog',
			'muze.util.pngfix',
			'muze.util.splitpane',
			'muze.ariadne.registry',
			'muze.ariadne.cookie',
			'muze.ariadne.explore',
			'muze.ariadne.selectable',
			'muze.ariadne.dropzone'
		);

		// only nls dependant var used atm.
		$JSnls = array();
		$JSnls["notfoundpath"] = $ARnls["notfoundpath"];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Ariadne<?php echo ": " . $AR->user->data->name . ": " . $objectName; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
<link href='//fonts.googleapis.com/css?family=Abel|Montserrat' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>button/assets/skins/sam/button.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>menu/assets/skins/sam/menu.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>treeview/assets/skins/sam/treeview.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>container/assets/skins/sam/container.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>datatable/assets/skins/sam/datatable.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>autocomplete/assets/skins/sam/autocomplete.css">

<script type="text/javascript" src="<?php echo $yui_base;?>yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>element/element-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>button/button-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>yahoo/yahoo-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>event/event-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>connection/connection-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>container/container_core-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>menu/menu-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>datasource/datasource-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>datatable/datatable-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>treeview/treeview-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>dragdrop/dragdrop-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>slider/slider-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>animation/animation-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>container/container-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base;?>autocomplete/autocomplete-min.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo $AR->dir->styles; ?>explore.css">
<!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="<?php echo $AR->dir->styles; ?>explore.ie6.css"><![endif]-->
<link rel="stylesheet" type="text/css" href="<?php echo $AR->dir->styles; ?>login.css">

<script type="text/javascript" src="<?php echo $this->make_local_url('',false,false) . 'ariadne.load.js?' . implode('+', $loadJS ); ?>"></script>

<script type="text/javascript">
	// Backwards compatibility hooks - these should be removed in the end.
	View = muze.ariadne.explore.view;
	LoadingDone = muze.ariadne.explore.loadingDone;
	objectadded = muze.ariadne.explore.objectadded;
	arEdit = muze.ariadne.explore.arEdit;
	updateChildren = muze.ariadne.explore.viewpane.update;
	selectItem = muze.ariadne.explore.viewpane.onSelectItem;
	Set = muze.ariadne.registry.set;
	Get = muze.ariadne.registry.get;
</script>

<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(muze.ariadne.explore.toolbar.init);
	YAHOO.util.Event.onDOMReady(muze.ariadne.explore.sidebar.getInvisiblesCookie);
	muze.ariadne.registry.set('browse_template','explore.browse.'); // intentionally ending in .
	muze.ariadne.registry.set('viewmode', '<?php echo $viewmode; ?>');

	//once the DOM has loaded, we can go ahead and set up our tree:
	YAHOO.util.Event.onDOMReady(muze.ariadne.explore.tree.init, muze.ariadne.explore.tree, true);
	YAHOO.util.Event.onDOMReady(muze.ariadne.explore.splitpane.init);
	YAHOO.util.Event.onDOMReady(muze.ariadne.explore.searchbar.init);

	YAHOO.util.Event.onDOMReady(function() {selectable.init("archildren", {filterClass:["explore_item","yui-dt-rec"]})});
</script>

<script type="text/javascript">
	// Pass the settings for the tree to javascript.
	muze.ariadne.explore.tree.loaderUrl 	= '<?php echo AddCSlashes( $loader, ARESCAPE ); ?>';

	muze.ariadne.explore.tree.baseNodes	= [
		{"path" : "<?php echo AddCSlashes( $path, ARESCAPE ); ?>", "name" : "<?php echo AddCSlashes( $name, ARESCAPE ); ?>", "icon" : "<?php echo AddCSlashes( $icon, ARESCAPE ); ?>"}
	];

	muze.ariadne.registry.set('ARRoot', '<?php echo AddCSlashes( $AR->root, ARESCAPE ); ?>');
	muze.ariadne.registry.set('root', '/');
	muze.ariadne.registry.set('store_root', '<?php echo AddCSlashes( $this->store->get_config('root'), ARESCAPE ); ?>');

	// setting session ID for unique naming of windows within one ariadne session.
	muze.ariadne.registry.set("SessionID","<?php echo AddCSlashes( $ARCurrent->session->id, ARESCAPE ); ?>");

	muze.ariadne.registry.set("path", "<?php echo AddCSlashes( $this->path, ARESCAPE ); ?>");
	muze.ariadne.nls = eval(<?php echo json_encode($JSnls); ?>);
<?php
	if( $AR->user->data->windowprefs["edit_object_layout"] ) {
		echo "\tmuze.ariadne.registry.set('window_new_layout', 1);\n";
	}
	if( $AR->user->data->windowprefs["edit_object_grants"] ) {
		echo "\tmuze.ariadne.registry.set('window_new_grants', 1);\n";
	}
?>
</script>
</head>

<body class="yui-skin-sam">
	<div id="explore_top">
		<?php
			$this->call("explore.toolbar.php");
		?>
	</div>

	<div id="explore_tree">
		<div id="treeDiv"></div>
		<div id="splitpane_slider"></div>
		<div id="splitpane_thumb"></div>
	</div>

	<div id="explore_managediv" class="managediv">
		<div id="sidebar" class="sidebar">
		<?php
			$this->call("explore.sidebar.php", $arCallArgs);
		?>
		</div>
		<div class="browse section" id="browseheader">
		<?php
			$this->call("explore.browse.header.php");
		?>
		</div>
		<div id="archildren">
		<?php
			$this->call("explore.browse.".$viewmode.".php");
		?>
		</div>
	</div>
</body>
</html>
<?php
	}
?>
