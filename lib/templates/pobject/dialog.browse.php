<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		global $AR;

		if (!$this->CheckSilent("layout")) {
			$AR->SVN->enabled = false;
		}

		include_once($this->store->get_config("code")."modules/mod_yui.php");

		$menuitems = array(
			array(
				'label' => $ARnls['ariadne:folders'],
				'iconalt' => $ARnls['ariadne:folders'],
				'icon' => $AR->dir->images . 'icons/small/view_tree.png',
				'href' => "#",
				'onclick' => 'document.body.classList.toggle("tree"); muze.ariadne.explore.tree.toggle(); return false;'
			),
			array(
				'iconalt' => $ARnls['ariadne:iconview'],
				'icon' => $AR->dir->images . 'icons/small/view_icon.png',
				'onclick' => 'return false;',
				'submenu' => array(
					array(
						'href' => "javascript:muze.ariadne.explore.viewpane.setviewmode('list');",
						'label' => $ARnls['ariadne:small'],
					),
					array(
						'href' => "javascript:muze.ariadne.explore.viewpane.setviewmode('icons');",
						'label' => $ARnls['ariadne:large'],
					),
					array(
						'href' => "javascript:muze.ariadne.explore.viewpane.setviewmode('details');",
						'label' => $ARnls['ariadne:details'],
					)
				)
			),
			array(
				'iconalt' => $ARnls['ariadne:up'],
				'icon' => $AR->dir->images . 'icons/small/up.png',
				//'href' => "javascript:muze.ariadne.explore.view('" . $this->parent . "');"
				'href' => $this->make_ariadne_url($this->parent) . "dialog.browse.php",
				'onclick' => "muze.ariadne.explore.toolbar.viewparent(); return false;",
				'id' => "viewparent"
			),
			array(
				'iconalt' => $ARnls['ariadne:delete'],
				'icon' => $AR->dir->images . 'icons/small/delete.png',
				'href' => $this->make_ariadne_url() . "dialog.delete.php",
				'id' => 'explore_toolbar_delete',
				'onclick' => "muze.ariadne.explore.dialog.deleteselected(this.href); return false;"
			),
			array(
				'iconalt' => $ARnls['ariadne:rename'],
				'icon' => $AR->dir->images . 'icons/small/rename.png',
				'href' => $this->make_ariadne_url() . "dialog.rename.php",
				'id' => 'explore_toolbar_rename',
				'onclick' => "muze.ariadne.explore.dialog.rename(this.href); return false;"
			)
		);

		/* retrieve HTTP GET variables */
		// FIXME: Er moet ook iets van een root zijn voor dit dialoog.

		$pathmode = $this->getvar("pathmode");
		if (!$pathmode && !$this->CheckAdmin($AR->user)) {
			$pathmode = "siterelative";
		}

		$root = $this->getvar("root");
		if (!$root) {
			$root = '/';
			if ($pathmode == "siterelative") {
				$root = $this->currentsite();
			}
		}

		$root = $this->make_path( $root );
		if ($root && $this->exists($root)) {
			$subPath = substr($this->path, strlen($root), -1);
			$subParticles = explode('/', $subPath);
			while ( !current( $this->get( $root, 'system.check.grant.phtml', array('grant' => 'read' ) ) ) && count( $subParticles )) {
				$root .= array_shift( $subParticles ) . '/';
			}
			$base_object = current( $this->get( $root, 'system.get.phtml' ) );
		} else {
			$root = "/";
			$base_object = current($this->get($this->currentproject(), "system.get.phtml"));
		}

		// This set initializes the tree from the user object
		$path 	= $base_object->path;
		$name 	= $base_object->nlsdata->name;
		$icon 	= $base_object->call('system.get.icon.php', array('size' => 'medium'));

		$loader = $this->store->get_config('root');
		$wwwroot = $AR->dir->www;
		$interface = $data->interface;

		$yui_base = $wwwroot . "js/yui/";
	//	$yui_base = "http://developer.yahoo.com/yui/";

		$viewmodes = array( "list" => 1, "details" => 1, "icons" => 1);
		$viewmode = $this->getvar("viewmode");
		if( !$viewmode || !$viewmodes[$viewmode] ) {
			$viewmode = ldGetUserCookie("viewmode");
			if( !$viewmode || !$viewmodes[$viewmode] ) {
				$viewmode = 'list';
			}
		}

		$ordering = array("name" => 1, "filename" => 1, "ctime" => 1, "mtime" => 1, "modified" => 1, "priority" => 1, "path" => 1); // List of allowed ordering options;
		$directions = array("asc" => 1, "desc" => 1);
		$order = strtolower($this->getvar('order'));
		if (!$order || !$ordering[$order]) {
			$order = 'name';
		}

		$direction = strtolower($this->getvar('direction'));
		if (!$direction || !$directions[$direction]) {
			$direction = 'asc';
		}


		$objectName = $nlsdata->name;
		if (!$objectName) {
			$objectName = $data->name;
		}

		$browsepath = $this->getvar('path');
		if (!$browsepath || !$this->exists($browsepath)) {
			$browsepath = $this->path;
		}

		$jail = ar::acquire('settings.jail');
		if ( !$jail ) {
			$jail = '/';
		}

		if ($pathmode == "siterelative") {
			$jail = $this->currentsite();
		}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Ariadne<?php echo ": " . $AR->user->data->name . ": " . $objectName; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
<!--link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>fonts/fonts-min.css"-->
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>button/assets/skins/sam/button.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>menu/assets/skins/sam/menu.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>treeview/assets/skins/sam/treeview.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>container/assets/skins/sam/container.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>datatable/assets/skins/sam/datatable.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base;?>treeview/assets/skins/sam/treeview.css">
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
<link rel="stylesheet" type="text/css" href="<?php echo $AR->dir->styles; ?>browse.css">
<link rel="stylesheet" type="text/css" href="<?php echo $AR->dir->styles; ?>wizard.css">
<!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="<?php echo $AR->dir->styles; ?>explore.ie6.css"><![endif]-->

<script type="text/javascript" src="<?php echo $wwwroot; ?>js/muze.js"></script>
<script type="text/javascript" src="<?php echo $wwwroot; ?>js/muze/event.js"></script>
<script type="text/javascript" src="<?php echo $wwwroot; ?>js/muze/dialog.js"></script>
<script type="text/javascript" src="<?php echo $wwwroot; ?>js/muze/util/pngfix.js"></script>
<script type="text/javascript" src="<?php echo $wwwroot; ?>js/muze/util/splitpane.js"></script>
<script type="text/javascript" src="<?php echo $wwwroot; ?>js/muze/ariadne/registry.js"></script>
<script type="text/javascript" src="<?php echo $wwwroot; ?>js/muze/ariadne/cookie.js"></script>
<script type="text/javascript" src="<?php echo $wwwroot; ?>js/muze/ariadne/explore.js"></script>
<script type="text/javascript" src="<?php echo $wwwroot; ?>js/muze/ariadne/selectable.js"></script>
<script type="text/javascript" src="<?php echo $wwwroot; ?>js/muze/ariadne/dropzone.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">
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
	muze.ariadne.registry.set('browse_template','explore.browse.');
	muze.ariadne.registry.set('viewmode', '<?php echo $viewmode; ?>');
	muze.ariadne.registry.set('order', '<?php echo $order; ?>');
	muze.ariadne.registry.set('direction', '<?php echo $direction; ?>');

	//once the DOM has loaded, we can go ahead and set up our tree:
	YAHOO.util.Event.onDOMReady(muze.ariadne.explore.tree.init, muze.ariadne.explore.tree, true);
	YAHOO.util.Event.onDOMReady(muze.ariadne.explore.splitpane.init);
	YAHOO.util.Event.onDOMReady(muze.ariadne.explore.searchbar.init);
	YAHOO.util.Event.onDOMReady(function() {selectable.init("archildren", {filterClass:["explore_item","yui-dt-rec"],touchSupport:false})});
</script>

<script type="text/javascript">
	// Pass the settings for the tree to javascript.
	muze.ariadne.explore.tree.loaderUrl 	= '<?php echo AddCSlashes( $loader, ARESCAPE ); ?>';

	muze.ariadne.explore.tree.baseNodes	= [{
			"path" : "<?php echo AddCSlashes( $path, ARESCAPE ); ?>",
			"name" : "<?php echo AddCSlashes( $name, ARESCAPE ); ?>",
			"icon" : "<?php echo AddCSlashes( $icon, ARESCAPE ); ?>"
	}];

	muze.ariadne.registry.set('root', '<?php echo AddCSlashes( $root, ARESCAPE ); ?>');
	muze.ariadne.registry.set('jail', '<?php echo AddCSlashes( $jail, ARESCAPE ); ?>');
	muze.ariadne.registry.set('ARRoot', '<?php echo AddCSlashes( $AR->root, ARESCAPE ); ?>');
	muze.ariadne.registry.set('store_root', '<?php echo AddCSlashes( $this->store->get_config('root'), ARESCAPE ); ?>');

	// setting session ID for unique naming of windows within one ariadne session.
	muze.ariadne.registry.set("SessionID","<?php echo AddCSlashes( $ARCurrent->session->id, ARESCAPE ); ?>");

	muze.ariadne.registry.set("path", "<?php echo AddCSlashes( $browsepath, ARESCAPE ); ?>");
<?php
	if( $AR->user->data->windowprefs["edit_object_layout"] ) {
		echo "\tmuze.ariadne.registry.set('window_new_layout', 1);\n";
	}
	if( $AR->user->data->windowprefs["edit_object_grants"] ) {
		echo "\tmuze.ariadne.registry.set('window_new_grants', 1);\n";
	}

	foreach ((array)$extraroots as $extrapath) {
		if ($extrapath != $path) {
			$extrapath_ob = current($this->get($extrapath, "system.get.phtml"));
			if ($extrapath_ob) {
				$extrapath_icon = $extrapath_ob->call('system.get.icon.php', array('size' => 'medium'));
				echo "\tmuze.ariadne.explore.tree.baseNodes.push({'path' : '" . AddCSlashes( $extrapath, ARESCAPE ) . "', 'name' : '" . AddCSlashes( $extrapath_ob->nlsdata->name, ARESCAPE ) . "', 'icon' : '" . AddCSlashes( $extrapath_icon, ARESCAPE ) . "'});";
			}
		}
	}

?>

</script>

<script type="text/javascript">
	function callback() {
		var path = muze.ariadne.explore.viewpane.selectedPath;
		if (!path) {
			path = muze.ariadne.registry.get("path");
		}

		if (window.opener.muze && window.opener.muze.dialog && window.opener.muze.dialog.hasCallback( window.name, 'submit') ) {
			window.opener.muze.dialog.callback( window.name, 'submit', {
				'path' : path
			} );
		} else if (window.opener && window.opener.callback) {
			window.opener.callback(path);
		} else {
			console.log("Opener window not found?");
		}
		window.close();
	}

	muze.event.attach(window, "load", function() {
		if ( document.getElementById('relativepath') ) {
			muze.event.attach(document.getElementById("relativepath"), "change", function() {
				var jail = document.getElementById("jail").value;
				var relativePath = this.value;
				document.getElementById("searchpath").value = jail + this.value;
			});
			muze.event.attach(document.getElementById("relativepath"), "keyup", function() {
				var jail = document.getElementById("jail").value;
				var relativePath = this.value;
				document.getElementById("searchpath").value = jail + this.value;
			});
		}

		muze.event.attach(window, "searchPathUpdated", function() {
			var newPath = document.getElementById("searchpath").value;
			if ( document.getElementById('relativepath') ) {
				var jail = document.getElementById("jail").value;
				if (jail) {
					var relativePath = newPath;
					if (newPath.indexOf(jail) == 0) {
						relativePath = newPath.substring(jail.length, newPath.length);
						document.getElementById("relativepath").value = relativePath;
					}
				}
			}

			// Fix the base URL for all the buttons in the toolbar to point to the new path;
			var targetButton = document.querySelectorAll("#explore_menubar a");
			for (var i=0; i<targetButton.length; i++) {
				if (targetButton[i].href && targetButton[i].href.indexOf(muze.ariadne.registry.get('store_root')) >= 0 ) {
						var targetHref = targetButton[i].href.substring(targetButton[i].href.lastIndexOf('/')+1 );
						targetButton[i].href = muze.ariadne.registry.get('store_root')+newPath+targetHref;
				}
			}
		});
	});
</script>
<style type="text/css">
	@media (max-width: 481px) {
		.yui-skin-sam .yuimenubaritemlabel {
			border: 0px;
		}
		#explore_top {
			height: 64px;
		}
		#explore_top div.searchdiv {
			left: 10px;
			top: 32px;
		}
		.managediv {
			margin-top: 65px;
		}
		#splitpane_thumb {
			display: none;
		}
		#explore_tree {
			width: 100% !important;
		}
		#explore_tree #treeDiv {
			width: auto !important;
			right: 0px !important;
		}
		#explore_managediv {
			left: 0 !important;
			transition: left 0.3s;
			-webkit-transition: left 0.3s;
		}
		#explore_tree #treeDiv {
			bottom: 34px;
		}
		body.tree #explore_managediv {
			left: 110% !important;
		}
	}
</style>
</head>

<body class="yui-skin-sam">
	<div id="header">
		<?php
			ar::call('ariadne.logo.html');
		?>
		<span class="text">Browse</span>
		<img class="typeicon" src="<?php echo $AR->dir->images; ?>icons/large/search.png" alt="Browse">
	</div>

	<div id="sectiondata" class="nosections">
		<div id="explore_top">
			<?php echo yui::yui_menuitems($menuitems, "yuimenubar", "explore_menubar")."\n"; ?>
			<div class="searchdiv">
				<form action="dialog.browse.php" onsubmit="muze.ariadne.explore.toolbar.searchsubmit(this.arPath.value); return false;">
					<div>
						<?php if ($pathmode == "siterelative") { ?>
							<input type="hidden" id="jail" name="jail" value="<?php echo $jail; ?>">
							<input type="hidden" id="searchpath" name="arPath" value="<?php echo $browsepath; ?>" onchange="console.log(this.value);">
							<input size="30" id="relativepath" class="searchpath" type="text" name="relativePath" value="<?php echo preg_replace("|^" . $jail . "|", "", $browsepath); ?>">
						<?php } else { ?>
							<input size="30" id="searchpath" class="text searchpath" type="text" name="arPath" value="<?php echo $browsepath; ?>">
						<?php } ?>
						<input type="image" src="<?php echo $AR->dir->www; ?>images/icons/small/go.png" title="<?php echo htmlspecialchars($ARnls['ariadne:search']); ?>" id="searchbutton" name="searchsubmit" value="<?php echo $ARnls["ariadne:search"]; ?>">
					</div>
					<div id="resultscontainer"></div>
				</form>
			</div>
		</div>

		<div id="explore_tree">
			<div id="treeDiv"></div>
			<div id="splitpane_slider"></div>
			<div id="splitpane_thumb"></div>
		</div>

		<div id="explore_managediv" class="managediv">
			<div class="browse" id="archildren">
				<?php
					$this->get($browsepath, "explore.browse.".$viewmode.".php");
				?>
			</div>
		</div>
	</div>
	<div class="explore_buttons">
		<form action="" onsubmit="callback(); return false;">
			<div class="buttons">
				<div class="left">
					<input type="file" multiple name="file[]" id="upload">
					<button for="upload" class="button" onclick="muze.event.fire(document.getElementById('upload'), 'click'); return false"><?php echo $ARnls['ariadne:uploader']; ?></button>
					<?php
					$extraButtonsEventData = new baseObject();
					$extraButtonsEventData = ar_events::fire( 'ariadne:onbeforebrowsebuttons', $extraButtonsEventData );
					if ($extraButtonsEventData) {
						$this->call("dialog.browse.buttons.html", array( "hideAdd" => $hideAdd ));
						$this->call("dialog.browse.buttons.extra.html");
						ar_events::fire('ariadne:onbrowsebuttons');
					}
				?></div>
				<div class="right">
					<input type='submit' value='<?php echo $ARnls['cancel']; ?>' onclick="window.close(); return false;">
					<input type='submit' value='<?php echo $ARnls['ariadne:browse:select']; ?>' onclick="callback(); return false;">
				</div>
			</div>
		</form>
	</div>
</body>
</html>
<?php
	}
?>
