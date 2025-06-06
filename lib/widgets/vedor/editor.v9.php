<?php
	global $ARCurrent,$ARnls;
	$js_root = $AR->dir->www."js/";
	$image_dir=$AR->dir->www.'widgets/vedor/images/';
	$script_dir=$AR->dir->www.'widgets/vedor/';
	$style_dir=$AR->dir->www.'widgets/vedor/';
	if (!$wgHTMLEditTemplate) {
		if ( $wgVedorEditTemplate ) {
			$wgHTMLEditTemplate = $wgVedorEditTemplate; // backwards compatible
		} else {
			$wgHTMLEditTemplate='user.edit.page.html';
		}
	}
	$language=$AR->user->data->language;
	if (!$AR->nls->list[$language]) {
		$language='en';
	}
	$language = preg_replace('/[^a-z0-9_]/i', '', $language);
	$ARnls->load('', $language);
	$ARnls->load('vedor-editor-v9', $language);

	$getargs = "?vdLanguage=" . RawURLEncode($language);
	// CAS vars;
	if ($requestorHost??null) {
		$getargs .= "&requestorHost=" . RawURLEncode($requestorHost);
	}
	if ($requestorPort??null) {
		$getargs .= "&requestorPort=" . RawURLEncode($requestorPort);
	}

	// load editor.ini, in case the editor is started directly, not through the
	// js.html file
	$oldnls=$this->nls;
	$this->setnls($language);
	$options=$this->call("editor.ini", ($arCallArgs ?? null));
	$vdBrowseRoot = $options['browse']['root'];
	if (!$vdBrowseRoot) {
		$vdBrowseRoot = $options['vdBrowseRoot']; // for backwards compat.
		if (!$vdBrowseRoot) {
			$vdBrowseRoot = $this->currentsite();
		}
	}
	$this->setnls($oldnls);
	if ( !($vedorPortalLink??null)) {
		$vedorPortalLink = 'http://www.vedor.nl/';
	}
	if ( !($wgHTMLEditManageTemplate??null) ) {
		$wgHTMLEditManageTemplate = 'user.edit.html';
	}
 	if ( !$options['doctype'] ) {
		$options['doctype'] = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
	}

	if ( !$options['editor-toolbars'] ) {
		$options['editor-toolbars'] = array(
			"toolbar.vedor-hyperlink.html",
			"toolbar.vedor-image.html",
			"toolbar.vedor-list-cursor.html",
			"toolbar.vedor-menu.html",
			"toolbar.vedor-text-cursor.html",
			"toolbar.vedor-text-selection.html",
		);
	}

	echo $options['doctype'];
?>
<html>
<head>
	<META content="text/html; charset=UTF-8" http-equiv=Content-Type>
	<meta http-equiv="x-ua-compatible" content="IE=edge">
	<title>Vedor WYSIWYG Editor</title>
	<link rel="stylesheet" href="<?php echo $style_dir; ?>editor.v9.css" type="text/css">
	<link rel="stylesheet" href="<?php echo $this->make_local_url(); ?>editor.overrides.css" type="text/css">
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">

	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<script type="text/javascript" src="ariadne.load.js?muze+muze.event+muze.form+muze.html+muze.dialog+muze.ariadne.cookie+muze.util.pngfix+muze.util.splitpane+muze.ariadne.registry+muze.ariadne.explore.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor.js"></script>

	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/dom.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/dom/selection.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/dom/nesting.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/dom/cleaner.js"></script>

	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/util.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/util/base64.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/util/undohandler.js"></script>

	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/widgets.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/widgets/fieldsets.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/widgets/handles.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/widgets/properties.js"></script>

	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor.js"></script>
	<!-- script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor/compose.js"></script -->
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor/polyfill/qsa-scope.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor/polyfill/selectionchange.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor/selection.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor/bookmarks.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor/styles.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor/paste.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor/keepalive.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor/contextbar.js"></script>
	<script type="text/javascript" src="<?php echo $js_root; ?>vedor/editor/toolbar.js"></script>
<script ID="editorSettings" LANGUAGE="JavaScript">
	var vdDebug = false;
	var rootURL='<?php echo $this->store->get_config("root"); ?>';
	var objectPath='<?php echo $this->path; ?>';
	var sitePath='<?php echo $this->currentsite(); ?>';
	var objectURL='<?php echo $this->make_local_url(); ?>';
	var logoffURL='<?php echo $this->make_local_url('', ($nls ?? null)); ?>';
	var objectURL_nls='<?php echo $this->make_local_url('',$language); ?>';
	var ariadneRoot='<?php global $AR; echo $AR->dir->www; ?>';
	var wgSaveTmpl='<?php echo $wgHTMLEditSaveTemplate; ?>';
	var wgParentURL='<?php echo $this->make_local_url($this->parent); ?>';
	var wgManageTmpl='<?php echo $wgHTMLEditManageTemplate; ?>';
	var vdCurrentSite='<?php echo $this->make_local_url($this->currentsite(), $language); ?>';
	var vdCurrentSiteNLS='<?php echo $this->make_local_url($this->currentsite(), ($nls ?? null)); ?>';
	var vdCurrentPath=objectPath;
	var vdBrowseRoot='<?php echo $vdBrowseRoot; ?>';
	var vdSelection = null;
	var vdSelectionState = null;
	var tbContentEditOptions = <?php echo json_encode($options); ?>;
	var vdOpenMetaPane = <?php echo ( ar::getvar('vdOpenMeta') ? 'true' : 'false' ); ?>;
	var vdEditPane;
	var vdMetaPane;
	var vdEditorCanvas;
	var vdEditorFrame;
	var vdContextBar=false;
	var vdUndoEnabled=true;
	var vdMetaDataSlideEnabled=false;
	var vdHtmlContextHides=false;
	var vdTableDesigner;
	var vdLanguages = <?php echo json_encode($AR->nls->list); ?>;

	var vdHandles;

	vedor.editor.keepalive.start();

	muze.ariadne.explore.view = function(path) {
		window.location=vdCurrentSite+path.replace(sitePath, '')+wgManageTmpl;
	}


	function setConfig(newconfig) {
		tbContentEditOptions = newconfig;
	}

	function window_onresize() {
		// check whether the editpane needs to be moved down
		// to make room for the toolbars
		var vdToolbars=document.getElementById('vdToolbars');
		var ToolbarHeight=vdToolbars.offsetHeight ? vdToolbars.offsetHeight : 52;

		if (window.getSelection ) { // FIXME: border in non-ie so we have to add 4
			ToolbarHeight+=4;
		}
		if (vdMetaDataSlideEnabled) {
			var mdHeight = document.getElementById('vdMetaDataSlide').offsetHeight;
			ToolbarHeight+=mdHeight + 4;
		}
		// check whether the edit pane has room for the contextbar
		var fullWidth= (document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.clientWidth );
		var fullHeight= (document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight );

		if (vdEditorFrame) {
			vdEditorFrame.style.width=(fullWidth-3)+'px';
			vdEditorFrame.style.height=(fullHeight-ToolbarHeight)+'px';
		}
		if (vdMetaDataSlideEnabled) {
			document.getElementById('vdMetaDataSlide').style.width = (fullWidth-3)+'px';
		}

		var savePopupX = Math.round( ( fullWidth - 300 ) / 2);
		var savePopupY = Math.round( ( fullHeight - 150 ) / 2);
		document.getElementById('vdSavePopup').style.left=savePopupX+'px';
		document.getElementById('vdSavePopup').style.top=savePopupY+'px';
	}

	//
	// Utility functions
	//

	function setFormat(command, value) {
		var blockRe=new RegExp('(H[1-7])|P');
		var skipExecCommand=false;
		var field=getEditableField();
		if (!field) {
			return;
		}
		registerChange(field.id);

		var sel = vdSelectionState.get();

		var target = vdEditPane.contentWindow.document;
		if( !window.getSelection && target.selection.type != "None" ) { // make sure we execCommand on the selection for IE.
			target = sel;
		}

		target.execCommand(command, false, value);

		vdSelectionState.restore();

		vdStoreUndo();
		vdEditPane_DisplayChanged();
		return true;
	}

	function setFormatStyle(styleInfo) {
		var field=getEditableField();
		if (!field) {
			return false;
		}

		vedor.editor.styles.init(vdEditPane.contentWindow);
		vedor.editor.styles.format(styleInfo, field);

		vdStoreUndo();

		vdEditPane_DisplayChanged();
		return true;
	}

	function getBlock(el, BlockElements) {
		if (!BlockElements) {
			BlockElements="|H1|H2|H3|H4|H5|H6|P|PRE|LI|TD|DIV|BLOCKQUOTE|DT|DD|TABLE|HR|IMG|";
		}
		while ((el!=null) && (BlockElements.indexOf("|"+el.tagName+"|")==-1)) {
			el=el.parentNode;
		}
		return el;
	}

	function getBlockFormat() {
		var result='';
		var sel = vdSelectionState.get();
		if ( sel && !vdSelectionState.getControlNode(sel) ) {
			var parentBlock=getBlock(vdSelection.parentNode(sel));
			if (parentBlock) {
				switch(parentBlock.tagName) {
					case 'LI':
						result=parentBlock.parentNode.tagName;
						if (parentBlock.parentNode.className) {
							result+='.'+parentBlock.parentNode.className;
						}
					default:
						result=parentBlock.tagName;
						if (parentBlock.className) {
							result+='.'+parentBlock.className;
						}
				}
			}
		}
		return result;
	}

	function tidy(html) {
		var d = document.createElement('div');
		d.innerHTML = html;
		return d.innerHTML;
	}

	function getSize(size) {
		if (size) {
			var sizeRE=new RegExp("([0-9]*[.]?[0-9]+)(%|em|ex|px|in|cm|mm|pi|pt)?","i");
			var sizeString=new String(size);
			var results=sizeString.match(sizeRE);
			if (results && results.length) {
				if (results.length==1) {
					results[1]='px';
				}
			}
		} else {
			var results=false;
		}
		return results;
	}


	/* backwards compatible property wrappers */
	function vdGetProperty(input_id) {
		return vedor.widgets.properties.get(input_id);
	}

	function vdSetProperty(input_id, value) {
		return vedor.widgets.properties.set(input_id, value);
	}

	function vdEnableProperty(input_id) {
		return vedor.widgets.properties.enable(input_id); // FIXME: this func supports vararg.
	}

	function vdDisableProperty(input_id) {
		return vedor.widgets.properties.disable(input_id); // FIXME: this func supports vararg.
	}

	function vdPropertyIsEnabled(input_id) {
		return vedor.widgets.properties.isEnabled(input_id);
	}

	function initImageProperties(image) {
		var type=image.getAttribute('ar:type');
		if (!type || type=='undefined') {
			// type='Origineel'; // FIXME: gewoon de eerste in de lijst selecteren?
			type = document.querySelector("#vdImageType option").value;
		}
		vdSetProperty('vdImageType',type);
		var alt=image.getAttribute('alt');
		vdSetProperty('vdImageAlt',alt);
		var title=image.getAttribute('title');
		vdSetProperty('vdImageAlt',title);

		var align = "none";
		if (image.classList.contains("vdLeft")) {
			align = "left";
		} else if (image.classList.contains("vdRight")) {
			align = "right";
		} else if (image.classList.contains("vdCenter")) {
			align = "center";
		}

		vdSetProperty('vdImageAlign', align);

		// Set the parent icon for alignment as well;
		var currentIcon = document.querySelector("div.vedor-image-align button.vedor-selected i");
		if (currentIcon) {
			var icons = document.querySelectorAll("button[data-vedor-section=vedor-image-align] i");
			for (var i=0; i<icons.length; i++) {
				icons[i].className = currentIcon.className;
			}
		}
	}

	function clearOptions(select) {
		try {
			for (var i=select.options.length-1; i>=0; i--) {
				select.options[i] = null;
			}
		} catch(e) {
		}
	}

	function loadOptions(selectId, options, selected) {
		// this code works around an obscure IE8 bug where it complains
		// that 'Option' is undefined once in a while.
		var select = document.getElementById(selectId);
		if (select) {
			clearOptions(select);
			if (document.all) { // IE (and Opera)
				var optionsList = '';
				for (var key in options) {
					var value = new String(options[key]);
					value = value.replace('"', '&quot;');
					var newOption = '<option value="' + value + '"';
					if (selected == key) {
						newOption += ' selected';
					}
					newOption += '>' + key + '</option>';
					optionsList += newOption;
				}
				var newSelect = new String(select.outerHTML);
				newSelect = newSelect.replace(/<\/SELECT>/i, optionsList+'</SELECT>');
				select.outerHTML = newSelect;
			} else { // run the pretty code
				for (var key in options) {
					select.options[select.options.length] = new Option(key, options[key]);
					if ( selected == key ) {
						select.options[select.options.length - 1].selected = true;
					}
				}
			}
		}
	}

	function preinit() {
		vdEditorFrame = document.getElementById('vdEditPane');
		vdEditorCanvas= vdEditorFrame.contentWindow;
		vdContextBar  = document.getElementById('vdContextBar');
		vdEditPane    = document.getElementById('vdEditPane');
		vdMetaPane    = document.getElementById('vdMetaFrame');
		vdUndoHandler = vedor.util.undohandler.init( restoreField );

		// muze.event.attach(vdEditorFrame, "load", checkLoad);
		muze.event.attach(vdEditorFrame, "load", initEditablePage);
		muze.event.attach(vdMetaPane, "load", initMeta);
		muze.event.attach(vdMetaPane, "initEditablePage", initEditablePage);
	}

	function initGroups(allScripts) {
		for (var i=0; i<allScripts.length; i++) {
			switch (allScripts[i].getAttribute("type")) {
				case "vedor/registerGroup":
					var vedorGroup = allScripts[i].getAttribute("data-vedor-group");
					var vedorId = allScripts[i].getAttribute("data-vedor-id");
					registerGroup(vedorGroup, vedorId);
				break;
			}
		}
	}

	function initEditablePage() {
		var allScripts = this.contentDocument.getElementsByTagName("SCRIPT");
		var resetScripts = new Array();
		var settingScripts = new Array();
		var groupScripts = new Array();
		var activeSection = document.querySelector(".vedor-section-active");

		if (activeSection) {
			activeSection.classList.remove("vedor-section-active");
		}

		for (var i=0; i<allScripts.length; i++) {
			switch (allScripts[i].getAttribute("type")) {
				case "vedor/reset":
					var vedorPath 		= allScripts[i].getAttribute("data-vedor-path");
					var vedorUrl  		= allScripts[i].getAttribute("data-vedor-url");
					var vedorParentUrl	= allScripts[i].getAttribute("data-vedor-parent-url");
					try {
						var vedorNlsList	= JSON.parse(allScripts[i].getAttribute("data-vedor-nls-list"));
					} catch(e) {
						var vedorNlsList	= [];	
					}
					var vedorLanguage	= allScripts[i].getAttribute("data-vedor-language");
					var vedorUrlNls		= allScripts[i].getAttribute("data-vedor-url-nls");
					var vedorSiteNls	= allScripts[i].getAttribute("data-vedor-site-nls");
					reset(vedorPath, vedorUrl, vedorParentUrl, vedorNlsList, vedorLanguage, vedorUrlNls, vedorSiteNls);
				break;
				case "vedor/registerGroup":
					/* Moved to own init, otherwise the groups are initialized too early; */
					// var vedorGroup = allScripts[i].getAttribute("data-vedor-group");
					// var vedorId = allScripts[i].getAttribute("data-vedor-id");
					// registerGroup(vedorGroup, vedorId);
				break;
				case "vedor/editorSettings":
					try {
						var editorSettings = JSON.parse(allScripts[i].innerHTML);
						// FIXME: add sanity check for settings;
						setConfig(editorSettings);
						init();
					} catch (e) {
						console.log("invalid editor settings");
						console.log(e);
						alert('Invalid editor.ini settings, cannot continue');
					}
				break;
				default:
					if ( vdDebug && allScripts[i].getAttribute("type") && allScripts[i].getAttribute("type").match(/^vedor/)) {
						console.log("unhandled script");
						console.log(allScripts[i].getAttribute('type'));
					}
				break;
			}
		}

		// Init groups last, to make sure all the fields are available;
		initGroups(allScripts);

		checkLoad();
		checkDuplicateEditableIds(this);
	}

	function vdHandleBrokenWebkitSelect(event) {
		var target = muze.event.target(event);
		if( target.tagName == 'IMG' ) {
			var selection = vdEditPane.contentWindow.document.defaultView.getSelection();
			selection.setBaseAndExtent(target, 0, target, 1);
			vdEditPane_DisplayChanged();
		}
	}

	function storeOption(option, value) {
		try {
			var options = JSON.parse(localStorage['vedorOptions']);
		} catch(e) {
		}
		if ( !options ) {
			options = {};
		}
		options[option] = value;
		localStorage['vedorOptions'] = JSON.stringify(options);
	}
	
	function getOption(option) {
		try {
			var options = JSON.parse(localStorage['vedorOptions']);
		} catch(e) {
		}
		if ( !options ) {
			options = {}
		}
		return options[option];
	}

	function init() {
			vdSelectionState = vedor.editor.selection;
			vdSelection = vedor.dom.selection;

			vdSelectionState.init(vdEditPane.contentWindow);
			selectionchange.start(vdEditPane.contentWindow.document); // onselectionchange event for Firefox

			//FIXME: is this the right place? Or should this be done in initEditable?
			arObjectRegistry=new Array();

			initEditable();

			vedor.editor.bookmarks.init(vdEditPane.contentWindow);

			// check if the stylesheet has .editable styles, if not, call VD_DETAILS_onclick to add them
			var vdEditDoc=vdEditPane.contentWindow.document;
			var foundit=false;
			var myStyleSheet = vdEditDoc.styleSheets[0];

			updateHtmlContext();

			addBordersStyleSheet(vdEditDoc);
			VD_DETAILS_onclick(getOption('borders')!==false);
			if ( vdEditDoc ) {
				vdEditDoc.documentElement.classList.add('vedor-editor');
				vdEditDoc.body.classList.add('vedor-editor');
			}

			if (tbContentEditOptions['grants']) {
				if (tbContentEditOptions['grants']['add']) {
					vdEnableButton("NEW");
				} else {
					vdDisableButton("NEW");
				}

				if (tbContentEditOptions['grants']['delete']) {
					vdEnableButton("DELETE");
				} else {
					vdDisableButton("DELETE");
				}
			}

			if (tbContentEditOptions['cookieConsentRequired']) {
				document.getElementById("vdCookieConsent").style.display = "block";
			} else {
				document.getElementById("vdCookieConsent").style.display = "none";
			}

			vedor.editor.paste.init(vdEditPane.contentWindow);
			vedor.editor.paste.attach( vdEditPane.contentWindow.document.body, function() {
				// register change in the editable field
				var editField=getEditableField();
				if (editField) {
					registerChange(editField.id);
					checkChangeStartEl(editField);
				}
			});

			//TODO: add compose widget again - once refactored
			//vedor.editor.compose.init( vdEditDoc, document.getElementById('vdComposePopup'), vdComposeComplete);
			muze.event.attach( vdEditDoc, 'Blur', function() { vdSelectionState.save(); return true; } ); // YES, blur is written with a capital B, intentional! Firefox does not support 'blur' on documents, only 'Blur'
			muze.event.attach( vdEditDoc, 'keydown', vdEditor_keydown);
			muze.event.attach( vdEditDoc, 'selectionchange', vdEditPane_DisplayChanged);
			muze.event.attach( vdEditDoc, 'keyup', vdEditPane_DisplayChanged); // mainly for FF and other nonselectionchange supporting browsers

			var vdMetaDataSlider = document.getElementById('vdMetaDataSlider');

			muze.event.attach( vdMetaDataSlider, 'mousedown', function( event ) {
				event = muze.event.get(event);
				// get current mouse y position
				var startY = event.screenY;
				var startHeight = document.getElementById('vdMetaDataSlide').offsetHeight;
				// append onmousemove handler
				var movehandler = muze.event.attach( document, 'mousemove', function(event) {
					event = muze.event.get(event);
					// get current mouse y position

					var newY = event.screenY;
					var diff = newY - startY;
					var newHeight = startHeight + diff;
					if (newHeight<20) {
						newHeight = 20;
					}
					if (newHeight>400) {
						newHeight = 400;
					}
					document.getElementById('vdMetaDataSlide').style.height = newHeight+'px';
					//window.status = 'sY: '+startY+'; nY: '+newY+'; sH: '+startHeight+'; nH: '+newHeight;
					window_onresize();
				} );
				var uphandler = null;
				uphandler = muze.event.attach( document, 'mouseup', function() {
					muze.event.detach(document, 'mouseup', uphandler);
					muze.event.detach(document, 'mousemove', movehandler);
					//document.onmousemove = null;
					//document.onmouseup = null;
					document.getElementById('editorPane').removeChild(document.getElementById('vdEventCatcher'));
				} );
				// insert hover div over full page, with transparent background
				var eventCatcher = document.createElement('DIV');
				eventCatcher.id = 'vdEventCatcher';
				document.getElementById('editorPane').appendChild(eventCatcher);
			} );


			// fill text options
			var vdTextStyles=document.getElementById('vdTextStyle');
			var vdTextStyleOptions = {};
			for (var i in tbContentEditOptions['css']['block']) {
				vdTextStyleOptions[tbContentEditOptions['css']['block'][i]] = i;
			}
			loadOptions('vdTextStyle', vdTextStyleOptions);
			var textStyles = document.querySelectorAll("select[name=textStyle]");
			for (var i=0; i<textStyles.length; i++) {
				textStyles[i].innerHTML = vdTextStyles.innerHTML;
			}

			// fill image options
			var vdImageTypes=document.getElementById('vdImageType');
			var vdImageOptions = { };
			for (var i in tbContentEditOptions['image']['styles']) {
				vdImageOptions[i] = i;
			}
			loadOptions('vdImageType', vdImageOptions);

			if (tbContentEditOptions['htmlblocks']) {
				var vdInsertPopup = document.getElementById('vdInsertPopup');
				var vdInsertContent = '';
				var count = 0;
				for (var i in tbContentEditOptions['htmlblocks']) {
						if (tbContentEditOptions['htmlblocks'][i]['icon']) {
							var icon = ' style="background-image: url('+tbContentEditOptions['htmlblocks'][i]['icon']+');"';
						} else {
							var icon = '';
						}
						var blockType = i.replace(/\./g, '-'); 
						vdInsertContent += '<a href="#" class="vdButtonLarge" unselectable="on" id="vdInsert-'+blockType+'" onClick="VD_INSERT_onclick(\''+i+'\')">'+
							'<div class="vdIcon" id="vdIcon-'+blockType+'"'+icon+'></div>'+tbContentEditOptions['htmlblocks'][i]['name']+'</a>';
						count++;
				}
				vdInsertPopup.innerHTML = vdInsertContent;
			}

			var imgOptionsDiv=document.getElementById('vdTabImage');
			if (imgOptionsDiv) {
				imgOptionsDiv.style.visibility='hidden'; //display='none';
			}

			vedor.widgets.fieldsets.init(document, '<?php echo $image_dir; ?>arrow_up.png', '<?php echo $image_dir; ?>arrow_down.png');


			// Run init for registered toolbars
			for (i in vedor.editor.toolbars) {
				if (vedor.editor.toolbars[i].init) {
					vedor.editor.toolbars[i].init();
				}
			}
			if ( getOption('tagBoundaries') ) {
				vedor.editor.actions['vedor-show-tags'](document.getElementById('vdShowTagBoundaries'));
			}
			if ( getOption('properties') || vdOpenMetaPane ) {
				vedor.editor.actions['vedor-properties'](document.getElementById('VD_META'),true);
			}
			if ( getOption('tagStack')===false ) { // default is on, this toggles it
				vedor.editor.actions['vedor-show-tags-stack'](document.getElementById('vdShowTagStack'));
			}
			if ( getOption('dockToolbars') ) {
				vedor.editor.actions['vedor-dock-toolbars'](document.getElementById('vdDockToolbars'));
			}
			window.onresize=window_onresize;
			vdEditPane.contentWindow.document.body.onbeforeunload = handleBeforeUnload; // must be set this way, don't use addEventListener/attachEvent
			vdEditPane.contentWindow.document.body.onunload = handleUnload;
			window_onresize();
	}

	// backwards compat
	function vdInitFieldsets( doc ) {
		if( !doc ) {
			doc = document;
		}
		vedor.widgets.fieldsets.init(doc, '<?php echo $image_dir; ?>arrow_up.png', '<?php echo $image_dir; ?>arrow_down.png');
	}


	function vdSetImage() {
		if (currentImage) {
			var type=vdGetProperty('vdImageType');
			currentImage.setAttribute('ar:type',type);
			if (tbContentEditOptions['image']['styles'][type]) {
				var className = currentImage.className;
				currentImage.className=tbContentEditOptions['image']['styles'][type]['class'];
				var temp=new String(currentImage.src);
				temp=temp.substr(0, temp.lastIndexOf('/')+1)+tbContentEditOptions['image']['styles'][type]['template'];
				currentImage.src=temp;
			}
			var align=vdGetProperty('vdImageAlign');
			if (align=='left') {
				currentImage.classList.add("vdLeft");
			} else if (align == "right") {
				currentImage.classList.add("vdRight");
			} else if (align == "center") {
				currentImage.classList.add("vdCenter");
			}

			var alt=vdGetProperty('vdImageAlt');
			if (alt) {
				currentImage.setAttribute('alt',alt);
			} else {
				currentImage.removeAttribute('alt');
			}
			var title=vdGetProperty('vdImageTitle');
			if (title) {
				currentImage.setAttribute('title',title);
			} else {
				currentImage.removeAttribute('title');
			}
			vdStoreUndo();
		}
	}

	function vdSetTextAlignClass() {
		var align=vdGetProperty('vdTextAlignClass');
		setFormatStyle('.'+align);
	}

	function reset(path, url, parenturl, nlslist, language, url_nls, site_nls) {
		// resets all variables to initial values
		objectPath=path;
		objectURL=url;
		if (url_nls) {
			objectURL_nls=url_nls;
		} else {
			objectURL_nls=objectURL;
		}
		if (site_nls) {
			vdCurrentSiteNLS = site_nls;
		}
		wgParentURL=parenturl;
		arFieldRegistry=new Array();
		arFieldList=new Array(); // simple array with all fields
		arObjectRegistry=new Array();
		arChangeRegistry=new Array();
		currentEditableField=false;

		// reset styles

		// FIXME: Text styles too;

		var vdImageTypes=document.getElementById('vdImageType');
		while (vdImageTypes.options.length) {
			vdImageTypes.options[vdImageTypes.options.length-1]=null;
		}

		// Run reset for registered toolbars
		for (i in vedor.editor.toolbars) {
			if (vedor.editor.toolbars[i].reset) {
				vedor.editor.toolbars[i].reset();
			}
		}
		if ( nlslist && nlslist[language] ) {
			language = nlslist[language]; // oldstyle url => Language
		} else {
			language = vdLanguages[language]; // newstyle nls => Language
		}
		// reset nls
		var vedorLanguageList = document.getElementById('vedorLanguageList');
		if ( vedorLanguageList && nlslist && Object.keys(nlslist).length>1 ) {
			vedorLanguageList.innerHTML = '';
			for ( var i in nlslist ) {
				var item = muze.html.el('li',
					muze.html.el(
						'button',
						{
							'data-vedor-link': i, 
							'data-vedor-language': nlslist[i], 
							'data-vedor-action': 'vedor-switch-language',
							class:'vedor-language-select' + ( language == nlslist[i] ? ' vedor-selected' : '' ) 
						},
						nlslist[i])
				);
				// FIXME: Deze appendchild moest ergens anders heen, maar waar?
				vedorLanguageList.appendChild(item);
			}
			//loadOptions('VD_NLS_SELECT', nlsOptions, nlsOptionsSelected);
			var languageButton = document.getElementById('vedorLanguage');
			languageButton.style.display = 'inline-block';
			if ( languageButton.style.display!='inline-block' ) { //Chrome sucks
				languageButton.style.cssText = 'display: inline-block;';
			}
		} else {
			document.getElementById('vedorLanguage').style.display = 'none';
		}
		vdUndoHandler.reset();
		//if ( url ) {
		//	document.getElementById('vdMetaFrame').src = url + 'dialog.vedor-meta.html?vdLanguage=<?php echo RawUrlEncode($language); ?>';
		//} else {
		//	document.getElementById('vdMetaFrame').src = 'about:blank';
		//}
	}


	// DisplayChanged handler. Very time-critical routine; this is called
	// every time a character is typed.

	var updateHtmlTimer = false;
	function vdEditPane_DisplayChanged() {
		if (skipContextUpdate) {
			return;
		}

		if (updateHtmlTimer) {
			window.clearTimeout(updateHtmlTimer);
		}
		updateHtmlTimer = window.setTimeout(function() {
			var field = getEditableField();
			if (field && !field.className.match(/\btext-only\b/)) {
				var blockFormat=getBlockFormat();
				if (blockFormat) {
					vdSelectStyle(blockFormat);
				}
				updateHtmlContext();
			} else {
				clearHtmlContext();
				updateHtmlContext();
			}
		}, 100);

		return true;
	}

	function vdEditor_keydown(evt) {
		var keyCode = (evt.charCode ? evt.charCode : ((evt.keyCode) ? evt.keyCode : evt.which));
		// 13 : return
		// 37, 38, 39, 40: arrow keys
		// 33, 34: pgup/dn
		// 35, 36: home/end
		if (keyCode==90 && evt.ctrlKey) { // ctrl-z : undo
			window.setTimeout(function() {
				VD_UNDO_onclick();
			}, 100);
			muze.event.cancel(evt);
		} else if (keyCode==89 && evt.ctrlKey) { // ctrl-y : redo
			window.setTimeout(function() {
				VD_REDO_onclick();
			}, 100);
			muze.event.cancel(evt);
		} else if ((keyCode==13) || ((keyCode<41) && (keyCode>32))) { // cursor etc, or return
			window.setTimeout(function() {
				vdStoreUndo();
			}, 100);
		}
		return true;
	}

	function vdComposeComplete(buffer) {
		var sel = vdSelectionState.get();
		if (sel) {
			vdSelection.setHTMLText(sel, buffer);
		}
		vdEditPane_DisplayChanged();
	}

	function getAjaxRequest() {
	   try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {}
	   try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {}
	   try { return new XMLHttpRequest(); } catch(e) {}
	   alert("XMLHttpRequest not supported");
	   return null;
	}

	function SAVE_onclick(newurl) {

		function showPopup() {
			document.getElementById('vdSaveMessage').innerHTML='<?php echo $ARnls['vd.editor:saving_message']; ?>';
			document.getElementById('vdSavePopup').style.display='block';
		}

		function hidePopup() {
			document.getElementById('vdSavePopup').style.display='none';
		}

		function dosave(newurl) {

			function vdEscape(arg) {
				return encodeURIComponent(arg);
			}

			var arguments='';
			if (!isDirty()) {
				return; // nothing to save
			} else {
				while (isDirty()) {
					field = getDirtyField();
					if (field) {
						var targetField = (vdEditPane.contentDocument.getElementById(field.fieldId) || vdMetaPane.contentDocument.getElementById(field.fieldId));
						if (targetField) {
							if (!targetField.name || (targetField.name == field.name)) {
								arguments+='changes['+escape(field.path)+']'+field.name+'='+vdEscape(getValue(field.fieldId))+'&';
							}
						}
					}
				}
				clearDirty();
			}
			if (arguments) {
			   <?php if ($ARCurrent->session) { ?>
				arguments+='formSecret=<?php echo $ARCurrent->session->data->formSecret; ?>&';
			   <?php } ?>

			   arguments+='postrequestiscompleet=1';
				showPopup();
				var ajax=getAjaxRequest();
				ajax.open("post", objectURL+"user.edit.save.ajax?t=" + new Date().getTime(), false);
				// synchronous request so we can wait for it to finish in other code and e.g. manually redirect
				// user can't do anything else while waiting for the page to be saved anyway
				ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
				/*ajax.onreadystatechange = function() {
					if (ajax.readyState != 4) { return; }
					var serverresponse = ajax.responseText;
					document.getElementById('vdSaveMessage').innerHTML='<?php echo $ARnls['vd.editor:savedone_message']; ?>';
					window.setTimeout(hidePopup,500);
					if (newurl) {
						window.location=newurl;
					}
				};*/
				ajax.send(arguments);
				var serverresponse = ajax.responseText;
				var saveerror;
				try {
					saveerror = JSON.parse(serverresponse);
				} catch (e) {
					// not a json response;
				}
				if (saveerror && saveerror['error']) {
					for (code in saveerror['error']) {
						alert(saveerror['error'][code]);
					}
					document.getElementById('vdSaveMessage').innerHTML=saveerror['error'][code];
				} else {
					document.getElementById('vdSaveMessage').innerHTML='<?php echo $ARnls['vd.editor:savedone_message']; ?>';
				}
				window.setTimeout(hidePopup,500);
				if (newurl) {
					window.location=newurl;
				}
			}
		}
		vdStoreUndo();
		if (vdEditPane.contentWindow.SAVE_onclick) {
			vdEditPane.contentWindow.SAVE_onclick(newurl);
		} else {
			dosave(newurl);
		}
	}

	function NEW_onclick() {
		if (tbContentEditOptions['grants'] && !tbContentEditOptions['grants']['add']) {
			return false;
		}

		if (vdEditPane.contentWindow.NEW_onclick) {
			vdEditPane.contentWindow.NEW_onclick();
		} else {
			muze.dialog.open( objectURL + 'dialog.add.php','dialog_add', { windowFeatures: muze.ariadne.explore.windowprops['dialog_add'] })
			.on('submit', function( arr ) {
				if (arr && arr['url'] && arr['type'] ) {
					var stayOnPage = [ 'pfile', 'pphoto', 'pshortcut', 'pbookmark' ];
					for ( var i=0; i<stayOnPage.length; i++) {
						if ( arr['type'] == stayOnPage[i] || arr['type'].substr( 0, stayOnPage[i].length + 1 ) == stayOnPage[i]+'.' ) {
							vdEditPane.contentWindow.location.reload(true); // refresh
							return; // do not navigate to these pages
						}
					}
					navigateTo(arr['url'] + wgManageTmpl);
				}
			})
			.always( function() {
				this.close();
			});
		}
	}

	function DELETE_onclick() {
		if (tbContentEditOptions['grants'] && !(tbContentEditOptions['grants']['delete'])) {
			return false;
		}

		if (vdEditPane.contentWindow.DELETE_onclick) {
			vdEditPane.contentWindow.DELETE_onclick();
		} else {
			var args=new Array();
			window.status=objectURL_nls;
			muze.dialog.open( objectURL + 'dialog.delete.php','dialog_delete', { windowFeatures: muze.ariadne.explore.windowprops['dialog_delete'] })
			.on( 'deleted', function (arr) {
				navigateTo(wgParentURL + wgManageTmpl);
			})
			.always( function() {
				this.close();
			});
		}
	}

	function COPY_onclick() {
		var args=new Array();
		window.status=objectURL_nls;
		muze.dialog.open( objectURL + 'dialog.copy.php?origin=copy','dialog_copy', { windowFeatures: muze.ariadne.explore.windowprops['dialog_copy'] })
		.on( 'copied', function (arr) {
			if (!arr['copyTargetUrl']) {
				var ajax=getAjaxRequest();
				ajax.open("get", objectURL+"vd.hyperlink.makeurl.ajax?linkpath="+escape(arr['copyTarget']), false);
				ajax.send(arguments);
				arr['copyTargetUrl'] = ajax.responseText;

			}
			navigateTo(arr['copyTargetUrl'] + wgManageTmpl);
		})
		.always( function() {
			this.close();
		});
	}

	function RENAME_onclick() {
		var args=new Array();
		window.status=objectURL_nls;
		muze.dialog.open( objectURL + 'dialog.rename.php?origin=rename&pathmode=filename','dialog_rename', { windowFeatures: muze.ariadne.explore.windowprops['dialog_rename'] })
		.on( 'renamed', function (arr) {
			if (!arr['url']) {
				var ajax=getAjaxRequest();
				ajax.open("get", "vd.hyperlink.makeurl.ajax?linkpath="+escape(arr['path']), false);
				ajax.send(arguments);
				arr['url'] = ajax.responseText;
			}
			navigateTo(arr['url'] + wgManageTmpl);
		})
		.always( function() {
			this.close();
		});
	}

	function MOVE_onclick() {
		var args=new Array();
		window.status=objectURL_nls;
		muze.dialog.open( objectURL + 'dialog.move.php?origin=move&pathmode=parent','dialog_move', { windowFeatures: muze.ariadne.explore.windowprops['dialog_move'] })
		.on( 'renamed', function (arr) {
			if (!arr['url']) {
				var ajax=getAjaxRequest();
				ajax.open("get", "vd.hyperlink.makeurl.ajax?linkpath="+escape(arr['path']), false);
				ajax.send(arguments);
				arr['url'] = ajax.responseText;
			}
			navigateTo(arr['url'] + wgManageTmpl);
		})
		.always( function() {
			this.close();
		});
	}

	function hideItem(path) {
		var arguments = 'arReturnPage='+objectURL+'user.edit.page.html&';
		<?php if ($ARCurrent->session) { ?>
			arguments+='formSecret=<?php echo $ARCurrent->session->data->formSecret; ?>&';
		<?php } ?>
		document.getElementById('vdEditPane').src = rootURL + path + 'priority.toggle.html?' + arguments;
	}

	function vdMenuHide_onclick() {
		var sel = vdSelectionState.get();
		var menuitem = vdSelection.getNode(sel);
		var path = menuitem.getAttribute("data-vedor-path");
		hideItem(path);
	}

	function vdMenuSort_onclick() {
		var args=new Array();
		window.status=objectURL_nls;


		var sel = vdSelectionState.get();
		var menuitem = vdSelection.getNode(sel);
		var path = menuitem.getAttribute("data-vedor-path");

		var sortPath = rootURL + path + "../" // FIXME: Read this from Menu attributes, if available;

//		muze.dialog.open( objectURL + 'sort.html','dialog_sort', { windowFeatures: muze.ariadne.explore.windowprops['dialog_browse'] })
		muze.dialog.open( sortPath + 'sort.html', 'dialog_sort', { windowFeatures: muze.ariadne.explore.windowprops['dialog_browse'] })
		.on( 'sorted', function (arr) {
			navigateTo(objectURL + wgManageTmpl);
		})
		.always( function() {
			// this.close();
		});
	}

	function vdHidePage_onclick() {
		hideItem(objectPath);
	}

	muze.namespace("muze.ariadne.explore");

	muze.ariadne.explore.view = function (path) {
		wgRecurseDone('delete');
	};

	function wgRecurseDone(action) {
		if (vdEditPane.contentWindow.wgRecurseDone) {
			vdEditPane.contentWindow.wgRecurseDone(action);
		} else {
			navigateTo(wgParentURL+wgManageTmpl);
			//window.location=wgParentURL+wgManageTmpl;
		}
	}

	function objectadded(type, name, path) {
		if (vdEditPane.contentWindow.objectadded) {
			vdEditPane.contentWindow.objectadded(type, name, path);
		} else {
			navigateTo(objectURL+wgManageTmpl);
			//window.location=objectURL+wgManageTmpl;
		}
	}

	function doConfirmSave() {
		return confirm("You have made changes to this page, do you wish to save these?");
	}

	function handleBeforeUnload(evt) {
		if (isDirty()) {
			var event = muze.event.get(evt);
			if ( event ) {
				event.returnValue='<?php echo $ARnls['vd.editor:exit_skip_save']; ?>';
				muze.event.cancel(event);
			}
			return '<?php echo $ARnls['vd.editor:exit_skip_save']; ?>';
			// "You have made changes to this page, if you leave these changes will not be saved.";
		}
	}

	function handleUnload() {
		// prevent funny stuff - onunload clear all fields and other data for the current page.
		reset();
	}

	function checkLoad() {
		if ( !objectURL ) {
			// reset isn't called by the page in vdEditPane
			// first hide the editor toolbars and such - the iframe content may be unreadable because of javascript security
			document.body.className = document.body.className + ' vdEditorHidden';
			// now try to reload the iframe as the main page
			try {
				top.location.href = vdEditorFrame.contentWindow.location.href;
			} catch(e) {
			}
		} else {
			// if you were on a non editor-aware page and somehow got back to one that is
			// it's probably impossible to get here, but in case it isn't...
			document.body.className = document.body.className.replace( /\bvdEditorHidden\b/g, '' );
		}
	}

	function checkDuplicateEditableIds(targetFrame) {
		var idElements = targetFrame.contentDocument.querySelectorAll(".editable[id]");
		for (var i=0; i<idElements.length; i++) {
			var ids = targetFrame.contentDocument.querySelectorAll('.editable[id="' + idElements[i].id + '"]');
			if (ids.length > 1 && (ids[0] == idElements[i])) {
				alert("WARNING!!\n\nThe editable page contains duplicate field IDs.\nThis WILL cause problems if you save this page!");
			}
		}
	}


	function VD_NLS_onclick() {
		vdEditPane.src=document.getElementById('VD_NLS_SELECT').value;
	}

	function VD_UNDO_onclick() {
		// check if current content of current field is different from last known stuff
		var field=getEditableField();
		if (field) {
			if (vdUndoHandler.currentid==vdUndoHandler.maxid) {
				var startC=field.startContent;
				if (startC!=getValue(field.id)) {
					vdStoreUndo();
				}
			}
		}
		vdHighlightUpdate(vdUndoHandler.undo());
		checkUndoRedo();
		if (vdHandles) {
			vdHandles.hide();
		}
	}

	function VD_UNDERLINE_onclick() {
		setFormat("Underline");
	}

	function VD_REDO_onclick() {
		vdHighlightUpdate(vdUndoHandler.redo());
		checkUndoRedo();
	}

	var vdHighlightRealColors = {};

	function vdHighlightUpdate(info) {
		var el = null;
		if (info && info['id']) {
//			el = vdEditPane.contentWindow.document.getElementById(info['id']);
//			if (!el) {
				el = vdMetaPane.contentWindow.document.getElementById(info['id']);
				if (el) {
					// open meta panel if closed
					if (!vdMetaDataSlideEnabled) {
						VD_META_onclick();
					}
					selectMetaTab(el);
				}
//			}
		}
		var startColor = '#FFFFCC';
		var startBorderColor = '#80FF00';
		var opacity = 0;
		var count = 10;
		var speed = 100;

		function selectMetaTab(el) {
			var tab = null;
			var p = el;
			while (p && p.tagName != 'DD') {
				p = p.parentNode;
			}
			if (p) {
				var tab = p.previousSibling;
			}
			if (tab) {
				vdMetaPane.contentWindow.vdDialog.tabs.select(tab);
			}
		}

		function parseColor(color, t) {
			/* From: http://www.meyerweb.com/eric/tools/color-blend/ */
			var m = 1;
			col = color.replace(/[\#rgb\(]*/,'');
			if (col.match(/,/)) {
				var num = col.split(',');
				var base = 10;
			} else {
				if (col.length == 3) {
					a = col.substr(0,1);
					b = col.substr(1,1);
					c = col.substr(2,1);
					col = a + a + b + b + c + c;
				}
				var num = new Array(col.substr(0,2),col.substr(2,2),col.substr(4,2));
				var base = 16;
			}
			if (t == 'rgbp') {
				m = 2.55
			}
			var ret = new Array(parseInt(num[0],base)*m,parseInt(num[1],base)*m,parseInt(num[2],base)*m);
			return(ret);
		}

		function fadeToColor(el, color) {
			var colors = new Array();
			var temp = '';
			first = parseColor(startColor, 'hex');
			last = parseColor(color, 'hex');
			colors[count] = startColor;
			for (i=0; i<count; i++) {
				temp = "rgb(";
				temp += parseInt(first[0]+(last[0]-first[0])/count*i);
				temp += ",";
				temp += parseInt(first[1]+(last[1]-first[1])/count*i);
				temp += ",";
				temp += parseInt(first[2]+(last[2]-first[2])/count*i);
				temp += ")";
				colors[count-i] = temp;
			}
			colors[0] = color;
			var fader = function() {
				el.style.backgroundColor = colors[count--];
				if (count>=0) {
					setTimeout(fader, speed);
				}
			}
			setTimeout(fader, speed);
		}

		function fadeBorder(el, color) {
			var colors = new Array();
			var temp = '';
			first = parseColor(startBorderColor, 'hex');
			last = parseColor(color); // apparantly always decimal
			colors[count] = startColor;
			for (i=0; i<count; i++) {
				temp = "rgb(";
				temp += parseInt(first[0]+(last[0]-first[0])/count*i);
				temp += ",";
				temp += parseInt(first[1]+(last[1]-first[1])/count*i);
				temp += ",";
				temp += parseInt(first[2]+(last[2]-first[2])/count*i);
				temp += ")";
				colors[count-i] = temp;
			}
			colors[0] = color;
			var fader = function() {
				el.style.borderColor = colors[count--];
				if (count>=0) {
					setTimeout(fader, speed);
				}
			}
			setTimeout(fader, speed);
		}

		if (el) {
			if (el.currentStyle.backgroundColor && el.currentStyle.backgroundColor!='transparent') {
				if (!vdHighlightRealColors[el.id]) {
					vdHighlightRealColors[el.id] = el.currentStyle.backgroundColor;
				}
				fadeToColor(el, vdHighlightRealColors[el.id]);
			} else {
				if (!vdHighlightRealColors[el.id]) {
					vdHighlightRealColors[el.id] = el.currenStyle.borderColor;
				}
				fadeBorder(el, vdHighlightRealColors[el.id]);
			}
		}
	}

	function VD_PASTE_onclick() {
		setFormat("Paste");
	}

	function VD_JUSTIFYRIGHT_onclick() {
		setFormat("JustifyRight");
	}

	function VD_JUSTIFYLEFT_onclick() {
		setFormat("JustifyLeft");
	}

	function VD_JUSTIFYCENTER_onclick() {
		setFormat("JustifyCenter");
	}
	function VD_JUSTIFYFULL_onclick() {
		setFormat("JustifyFull");
	}
	function VD_INDENT_onclick() {
		setFormat("Indent");
	}
	function VD_OUTDENT_onclick() {
		setFormat("Outdent");
	}
	function VD_ITALIC_onclick() {
		replaceNodeTags("EM", "I");
		setFormat("Italic");
		replaceNodeTags("I", "EM");
	}

	function VD_IMAGE_onclick() {
		var args = new Array();
		var elIMG = false;
		var el = false;
		var rg = false;

		if (isEditable()) {
			window.el=false;
			window.elIMG=false;
			window.rg=false;
			el = vdSelectionState.get();
			window.el=el;
			elIMG = vdSelectionState.getControlNode(el);
			if (elIMG) {
				if (elIMG && elIMG.tagName=='IMG') {
					window.elIMG=elIMG;
				} else {
					window.elIMG=false;
					window.rg=el;
				}
			} else {
				window.elIMG=false;
				window.rg=el;
			}

			var type = document.querySelector("#vdImageType option").value;
			if (tbContentEditOptions['image']['default']) {
				type = tbContentEditOptions['image']['default'];
			}

			var url = objectURL + 'dialog.browse.php<?php echo $getargs; ?>&viewmode=icons&root=' + (tbContentEditOptions['photobook']['location'] ? tbContentEditOptions['photobook']['location'] : sitePath + "images/") + '&extraroots=' + sitePath + '&path=' + (tbContentEditOptions['photobook']['location'] ? tbContentEditOptions['photobook']['location'] : sitePath + "images/") + '&pathmode=siterelative';
			muze.dialog.open( url, 'sitemap', { windowFeatures : muze.ariadne.explore.windowprops['dialog_browse'] } )
			.on('submit', function( arr ) {
				if (arr && arr['path']) {
					var ajax=getAjaxRequest();
					ajax.open("get", objectURL+"vd.hyperlink.makeurl.ajax?linkpath="+escape(arr['path']), false);
					ajax.send(arguments);
					arr['src'] = ajax.responseText;

					if (tbContentEditOptions['image']['styles'][type]) {
						var temp=arr['src'];
						temp=temp.substr(0, temp.lastIndexOf('/')+1)+tbContentEditOptions['image']['styles'][type]['template'];
						arr['src']=temp;
					}
					var time = new Date();
					arr['src'] = arr['src'].replace(/(.*)\?t=.*$/, '$1') + "?t=" + time.getTime();
					IMAGE_set(arr);
				}
			})
			.always( function() {
				this.close();
			});
		}
	}

	function VD_IMAGE_EDIT_onclick() {
		if (currentImage) {
			var imageURL = currentImage.src.replace(/(.*)\/.*?$/, '$1') + '/';
			var args = new Array();

			var url = imageURL + 'aviary.edit';
			muze.dialog.open( url, 'sitemap', { windowFeatures : muze.ariadne.explore.windowprops['dialog_browse'] } )
			.on('submit', function( ) {
				var time = new Date();
				currentImage.src = currentImage.src.replace(/(.*)\?t=.*$/, '$1') + "?t=" + time.getTime();
				updateHtmlContext();

			})
			.always( function() {
				this.close();
			});
		}
	}

	function IMAGE_set(arr) {
		window.setfocusto=false;
		var el=window.el;
		if (arr && arr['src']) {
			// register change in the editable field, since the focus was already lost through the dialog
			var editField=getEditableField();
			if (editField) {
				registerChange(editField.id);
				checkChangeStartEl(editField);
			}

			src=new String(arr['src']);
			var temp1=new String('https://');
			var temp2=new String('http://');
			var temp3=new String('//');

			if (
				(src.substring(0,temp1.length)!=temp1) &&
				(src.substring(0,temp2.length)!=temp2) &&
				(src.substring(0,temp3.length)!=temp3)
			) {
				src=rootURL+src;
			}
			if (arr['ar:type'] && arr['ar:type']!='undefined') {
				src+=tbContentEditOptions['image']['styles'][arr['ar:type']]['template'];
			}
			if (window.elIMG) { // modify existing image;
				elIMG=window.elIMG;
				elIMG.src=src;
				if (arr['alt']) {
					elIMG.alt=arr['alt'];
				}
				if (arr['title']) {
					elIMG.title=arr['title'];
				}
				if (arr['ar:type']) {
					elIMG.setAttribute('ar:type',arr['ar:type']);
				}
				if (arr['path']) {
					elIMG.setAttribute('ar:path',arr['path']);
				} else {
					elIMG.setAttribute('ar:path',arr['ar:path']);
				}
				if (arr['class']) {
					elIMG.className = arr['class'];
				}
			} else { // insert a new image;
				el=window.el;
				temp='<IMG SRC="'+src+'"';
				if (arr['alt']) {
					temp+=' ALT="'+arr['alt']+'"';
				}
				if (arr['alt']) {
					temp+=' TITLE="'+arr['title']+'"';
				}
				if (arr['class']) {
					temp+=' CLASS="'+arr['class']+'"';
				}
				if (arr['ar:type']) {
					temp+=' ar:type="'+arr['ar:type']+'"';
				}
				if (arr['path']) {
					temp+=' ar:path="'+arr['path']+'"';
				} else if (arr['ar:path']!='') {
					temp+=' ar:path="'+arr['ar:path']+'"';
				}
				temp+='>';
				var control = vdSelectionState.getControlNode(el);
				if (!control) {
					vdSelection.setHTMLText(el, temp);
					vdSelectionState.restore();
				} else {
					if( control.outerHTML ) {
						control.outerHTML = temp;
					} else {
						div = control.ownerDocument.createElement('div');
						div.innerHTML = temp;
						var frag = control.ownerDocument.createDocumentFragment();
						for (var i=0; i < div.childNodes.length; i++) {
							var node = div.childNodes[i].cloneNode(true);
							frag.appendChild(node);
						}
						div = null;
						control.parentNode.replaceChild(frag, control);
					}
				}
			}
			// register change in the editable field, since the focus was already lost through the dialog
			if (editField) {
				checkChangeEndEl(editField);
			}
			vdStoreUndo();
		}
	}

	function VD_INSERT_onclick(insert_type) {
		// Close popup
		vdToggleInsert();

		if (tbContentEditOptions['htmlblocks'][insert_type]) {
			if (isEditable()) {
				var editField = getEditableField();
				var sel = vdSelectionState.get();
				var src = objectPath;
				var arr = null;
				var arguments='';
				if (tbContentEditOptions['htmlblocks'][insert_type]['dialog']) {
					var dialog = tbContentEditOptions['htmlblocks'][insert_type]['dialog'];
					var args = new Array();
					args['src'] = src;
					args['editOptions']=tbContentEditOptions;
					args['stylesheet']=tbContentEditOptions['css']['stylesheet'];
					// args = new Array();
					arr = showModalDialog( objectURL + dialog + "<?php echo $getargs; ?>", args,	"font-family:Verdana; font-size:12; dialogWidth:610px; dialogHeight:400px; status: no; resizable: yes;");
					var found = false;
					if (arr) {
						for (var i in arr) {
							arguments += escape(i) + '=' + vdEscape(arr[i])+'&';
							found = true;
						}
					}

					if (!found) {
						return false;
					}
				}
				// now do an ajax call to 'template', and insert the results.

				function vdEscape(arg) {
					if (arg.replace) {
						return arg.replace(/&/g, encodeURIComponent('&')).replace(/=/g, encodeURIComponent('=')).replace(/ /g, encodeURIComponent(' ')).replace(/\+/g, encodeURIComponent('+')).replace(/</g, encodeURIComponent('<')).replace(/>/g,encodeURIComponent('>'));
					} else {
						return arg;
					}
				}


				var ajax=getAjaxRequest();
				var now = new Date;
				ajax.open("post", objectURL+tbContentEditOptions['htmlblocks'][insert_type]['template']+'?'+now.getTime(), true);
				ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
				ajax.onreadystatechange = function() {
					if (ajax.readyState != 4 || ajax.status != 200) { return; }
					var serverresponse = ajax.responseText;
					// insert the serverresponse, only if status = 200
					var control = vdSelectionState.getControlNode(sel);
					if (!control)	{
						vdSelection.setHTMLText(sel, serverresponse);
						vdSelectionState.restore();
					} else {
						if( control.outerHTML ) {
							control.outerHTML = serverresponse;
						} else {
							div = control.ownerDocument.createElement('div');
							div.innerHTML = temp;
							var frag = control.ownerDocument.createDocumentFragment();
							for (var i=0; i < div.childNodes.length; i++) {
								var node = div.childNodes[i].cloneNode(true);
								frag.appendChild(node);
							}
							div = null;
							control.parentNode.replaceChild(frag, control);
						}
					}
					// register change in the editable field, since the focus was already lost through the dialog
					if (editField) {
						checkChangeEndEl(editField);
					}
					vdStoreUndo();
					vdEditPane_DisplayChanged();
					muze.event.fire(vdEditPane, "vedor-htmlblock-inserted");
					muze.event.fire(vdEditPane, "vedor-selectable-inserted");
				};
				ajax.send(arguments);
			}
		}
	}

	function vdToggleInsert() {
		var vdInsertPopup = document.getElementById('vdInsertPopup');
		var vdEditDoc=vdEditPane.contentWindow.document;
		if (vdInsertPopup.style.display=='none') {
			vdInsertPopup.style.display='block';
			var closeDropEvent;
			function closeDropDown() {
				muze.event.detach(vdEditDoc, 'click', closeDropEvent);
				document.getElementById('vdInsertPopup').style.display='none';
				return true;
			}
			window.setTimeout(function() {
				closeDropEvent = muze.event.attach(vdEditDoc, 'click', closeDropDown);
			}, 100);
		} else {
			vdInsertPopup.style.display='none';
			muze.event.detach(vdEditDoc, 'click', closeDropEvent);
		}
	}

	function VD_SYMBOL_onclick() {
		var field=getEditableField();
		if (field) {
			if( vedor.editor.compose.isActive() ) {
				vedor.editor.compose.stop();
			} else {
				vedor.editor.compose.start();
			}
		}
	}

	function vdGetParam(ob, name) {
		var param=ob.firstChild;
		while (param) {
			if (param.tagName=='PARAM') {
				if (param.name==name) {
					return param.value;
				}
			}
			param=param.nextSibling;
		}
	}

	function VD_HYPERLINK_onclick() {
		if (isEditable()) {
			var arr,args,oSel, oParent;
			var oATagList = new Array();

			oSel = vdSelectionState.get();
			var control = vdSelectionState.getControlNode(oSel);
			if (control) {
				oElement=control;
				oParent=oElement.parentNode;
			} else {
				if( oSel.select ) { // IE only
					var htmlText = vdSelection.getHTMLText(oSel);
					if (htmlText.substr(htmlText.length-4, 4)=='<BR>') {
						// BR included in selection as last element, remove it, it has
						// dangerous effects on the hyperlink command in IE
						oSel.moveEnd('character',-1);
						oSel.select();
					}
					if (htmlText.substr(0,4)=='<BR>') {
						// idem when its the first character
						oSel.moveStart('character',1);
						oSel.select();
					}
				}
				oParent = vdSelection.parentNode(oSel);
			}

			arr=null;
			args=new Array();
			//set a default value for your link button
			args["URL"] = "http:/"+"/";
			args["anchors"] = HYPERLINK_getAnchors();
			args['vdCurrentSite'] = vdCurrentSite;
			args['vdCurrentPath'] = sitePath;
			args['vdStartpath'] = vdBrowseRoot;
			args['objectPath'] = objectPath;
			args['objectURL'] = objectURL;
			args['objectURL_nls'] = objectURL_nls;
			if (oParent.tagName=="A") {
				oATagList.push(oParent);
				args["url"] = oParent.href;
				args['name'] = oParent.name;
				for (var i=0; i<oParent.attributes.length; i++) {
					oAttr=oParent.attributes.item(i);
					if (oAttr.specified) {
						args[oAttr.nodeName.replace(':', '')]=oAttr.nodeValue;
					}
				}
			}
			var urlArgs = new String();
			for (var key in args) {
				urlArgs += '&' + key + '=' + escape( args[ key ] );
			}
			/*
			here popup your own dialog, pass the arg array to that, get what the user
			entered there and come back here
			*/
//			arr = showModalDialog( objectURL_nls+"edit.object.html.link.phtml", args, "font-family:Verdana; font-size:12; dialogWidth:32em; dialogHeight:15em; status: no; resizable: yes;");
//			arr = showModalDialog( vdCurrentSite+"vd.hyperlink.phtml<?php echo $getargs; ?>", args, "font-family:Verdana; font-size:12; dialogWidth:610px; dialogHeight:400px; status: no; resizable: yes;");

			// FIXME: Remove this line;
			if (!muze.ariadne.explore.windowprops['dialog_hyperlink']) {
				muze.ariadne.explore.windowprops['dialog_hyperlink'] = muze.ariadne.explore.windowprops['dialog_edit'];
			}
			// vdSelectionState.save();
			var url = objectURL + 'dialog.hyperlink.php?root=' + (tbContentEditOptions['browse']['root'] ? tbContentEditOptions['browse']['root'] : sitePath ) + urlArgs;
			muze.dialog.open( url, 'hyperlink', { windowFeatures :  muze.ariadne.explore.windowprops['dialog_hyperlink'] } )
			.on('submit', function( arr ) {
				if (arr) {
					// register change in the editable field, since the focus was already lost through the dialog
					var editField=getEditableField();
					if (editField) {
						checkChangeStartEl(editField);
						registerChange(editField.id);
	//					editField.onfocus();
					}

					var linkclass = '';
					var newLink="<a";

					if (arr['href']) {
						newLink+=" href=\""+arr['href']+"\"";
					}
					if (arr['name']) {
						newLink+=' name="'+arr['name']+'"';
					}
					if (arr['attributes']) {
						for (var i in arr['attributes']) {
							var arAttributeValue=arr['attributes'][i];
							if (arAttributeValue) {
								if (i == "ar:type") {
									linkclass = linkclass + arAttributeValue;
								}
								newLink=newLink+" "+i+"=\""+arAttributeValue+"\"";
							}
						}
					}

					newLink = newLink + ' class="'+ linkclass + '"';
					newLink=newLink+">";

					vdSelectionState.restore(oSel);

					if (!oATagList.length && (arr['href'] || arr['name'])) {
						if ( control ) {
							if( false && oElement.outerHTML ) {
								oElement.outerHTML=newLink+oElement.outerHTML+"</A>";
							} else { // firefox and co
								var div = oElement.ownerDocument.createElement('div');
								var clone = oElement.cloneNode(true);
								div.appendChild(clone);
								var inner = div.innerHTML;
								div = oElement.ownerDocument.createElement('div');
								div.innerHTML = newLink+inner+'</a>';
								var frag = oElement.ownerDocument.createDocumentFragment();
								for (var i=0; i < div.childNodes.length; i++) {
									var node = div.childNodes[i].cloneNode(true);
									frag.appendChild(node);
								}
								div = null;
								oElement.parentNode.replaceChild(frag, oElement);
							}
						} else {
							// first let the dhtmledit component set the link, since it is better in it.
							// but to find it back, we need a unique identifier

							var linkIdentifier=Math.floor(Math.random()*10000);
							setFormat("CreateLink", '#'+linkIdentifier);
							var oATags = vdEditPane.contentDocument.querySelectorAll("A[href='#" + linkIdentifier + "']");

							for (var i=0; i<oATags.length; i++) {
								oATagList.push(oATags[i]);
							}
						}
					}

					// Now set the link properties for all the elements in the list.
					for (var j=0; j<oATagList.length; j++) {
						oATag = oATagList[j];

						if (oATag && (arr['href'] || arr['name'])) {
							if( false && oATag.outerHTML ) {
								oATag.outerHTML=newLink+oATag.innerHTML+'</a>';
							} else { // firefox and co
								var div = oATag.ownerDocument.createElement('div');
								div.innerHTML = newLink+oATag.innerHTML+'</a>';
								var frag = oATag.ownerDocument.createDocumentFragment();
								for (var i=0; i < div.childNodes.length; i++) {
									var node = div.childNodes[i].cloneNode(true);
									frag.appendChild(node);
								}
								div = null;
								oATag.parentNode.replaceChild(frag, oATag);
							}
						}
						if (oATag && (arr['href'] == '') && (arr['name'] == '')) {
							if( false && oATag.outerHTML ) {
								oATag.outerHTML=oATag.innerHTML;
							} else { // firefox and co
								var div = oATag.ownerDocument.createElement('div');
								div.innerHTML = oATag.innerHTML;
								var frag = oATag.ownerDocument.createDocumentFragment();
								for (var i=0; i < div.childNodes.length; i++) {
									var node = div.childNodes[i].cloneNode(true);
									frag.appendChild(node);
								}
								div = null;
								oATag.parentNode.replaceChild(frag, oATag);
							}
						}
					}
					if (editField) {
						checkChangeEndEl(editField);
					}
					vdStoreUndo();


				}
			})
			.always( function() {
				this.close();
			});

		}
		vdEditPane.focus();
	}

	function HYPERLINK_getAnchors() {
		var aATags = vdEditPane.contentWindow.document.getElementsByTagName('A');
		var result = new Array();
		var i=0;
		var ii=0;
		for (ii=0; ii<aATags.length; ii++) {
			var oATag=aATags[ii];
			if (oATag.name) {
				result[i]='#'+oATag.name;
				i++;
			}
		}
		return result;
	}

	function VD_CUT_onclick() {
		setFormat("Cut");
	}

	function VD_COPY_onclick() {
		setFormat("Copy");
	}

	function replaceNodeTags(source, target) {
		var field = getEditableField();
		if (!field) {
			return;
		}
		var sel = vdSelectionState.get();
		vedor.editor.bookmarks.set(sel);

		var elms = field.querySelectorAll(source);
		for (var i=0; i<elms.length; i++) {
			var newNode = document.createElement(target);
			newNode.innerHTML = elms[i].innerHTML;

			elms[i].parentNode.replaceChild(newNode, elms[i]);
		}

		vedor.editor.bookmarks.select();
		vedor.editor.bookmarks.remove();
	}

	function VD_BOLD_onclick() {
		replaceNodeTags("STRONG", "B");
		setFormat("Bold");
		replaceNodeTags("B", "STRONG");
	}

	/*
		Content retrieval methods for saving
	*/

	function getValue(data_name) {
		var data="";
		var value='';
		var srcDocument = vdEditPane.contentWindow.document;
		data = srcDocument.getElementById(data_name);
		if (!data) {
			srcDocument = vdMetaPane.contentWindow.document;
			data = srcDocument.getElementById(data_name);
		}
		if (data) {
			switch (data.type) {
				case 'checkbox' :
					if (data.checked) {
						value=data.value;
					}
					break;
				case 'radio' :
					var radios = srcDocument.getElementsByName( data.name );
					for (var i=0; i < radios.length; i++) {
						if (radios[i].checked) {
							value=radios[i].value;
							break;
						}
					}
					break;
				case 'hidden' :
				case 'password' :
				case 'text' :
				case 'textarea' :
					value=data.value;
					break;
				case 'select-one' :
					value=data.options[data.selectedIndex].value;
					break;
				case 'select-multiple' :
					value=new Array();
					for (var i=0; i<data.length; i++) {
						if (data.options[i].selected) {
							value[value.length]=data.options[i].value;
						}
					}
					break;
				default :
					if (data.className.match(/\btext-only\b/)) {
						value= ( typeof data.textContent != "undefined" ) ? data.textContent : data.innerText;
					} else {
						value=data.innerHTML;
					}
					break;
			}
			return value;
		} else {
			return '';
		}
	}

	function setValue(data_name, value) {
		var data="";
		var srcDocument = vdEditPane.contentWindow.document;

		data = srcDocument.getElementById(data_name);
		if (!data) {
			srcDocument = vdMetaPane.contentWindow.document;
			data = srcDocument.getElementById(data_name);
		}
		if (data) {
			switch (data.type) {
				case 'checkbox' :
					if (data.value == value) {
						data.checked = true;
					} else {
						data.checked = false;
					}
					break;
				case 'radio' :
					radios = srcDocument.getElementsByName( data.name );
					for (var i = radios.length - 1; i >= 0; i--) {
						if (value == radios[i].value) {
							radios[i].checked = true;
							break;
						}
					}
					break;
				case 'hidden' :
				case 'password' :
				case 'text' :
					data.value = value;
					break;
				case 'select-one' :
					data.options[data.selectedIndex].value = value;
					break;
				case 'select-multiple' :
					for (var i=0; i<value.length; i++) {
						for (var ii=0; ii<data.length; ii++) {
							if (data.options[ii].value == value[i]) {
								data.options[ii].selected = true;
								break;
							}
						}
					}
					break;
				default :
					if (data.className.match(/\btext-only\b/)) {
						if (typeof data.textContent != "undefined" ) {
							data.textContent = value;
						} else {
							data.innerText = value;
						}
					} else {
						data.innerHTML = value;
					}
					break;
			}
		}
	}

	var arFieldRegistry	= new Array();
	var arFieldList		= new Array(); // simple array with all fields
	var arObjectRegistry	= new Array();
	var arChangeRegistry	= new Array();
	var arGroupRegistry	= new Array();
	var arGroupRegistryQueue = new Array();
	var currentEditableField= false;

	function registerDataField(fieldId, fieldName, objectPath, objectId) {
		arFieldRegistry[fieldId]=new dataField(fieldId, fieldName, objectPath, objectId);
                if ( arGroupRegistryQueue[fieldId] ) {
                        arFieldRegistry[fieldId].group = arGroupRegistryQueue[fieldId];
                        arGroupRegistryQueue[fieldId] = null;
                }

		if (!arObjectRegistry[objectId]) {
			arObjectRegistry[objectId]=new Array();
		}
		if (!arObjectRegistry[objectId][fieldName]) {
			arObjectRegistry[objectId][fieldName]=new Array();
		}
		arObjectRegistry[objectId][fieldName][arObjectRegistry[objectId][fieldName].length]=arFieldRegistry[fieldId];
		arFieldList.push(arFieldRegistry[fieldId]);
	}

	function dataField(fieldid, name, path, id) {
		this.fieldId=fieldid;
		this.name=name;
		this.path=path; //FIXME: an object may have multiple paths, not all of which the user may have edit grants on
		this.id=id;
	}

	function registerGroup(group, fieldId) {
		if (!arGroupRegistry[group]) {
			arGroupRegistry[group]=new Array();
		}
		arGroupRegistry[group].push(fieldId);
		if (arFieldRegistry[fieldId]) {
			arFieldRegistry[fieldId].group=group;
		} else {
			arGroupRegistryQueue[fieldId] = group;
		}
	}

	function registerChange(fieldId, stoprecurse) {
		if (arFieldRegistry[fieldId]) {
			var objectId=arFieldRegistry[fieldId].id;
			var fieldName=arFieldRegistry[fieldId].name;
			var field=vdEditPane.contentWindow.document.getElementById(fieldId);
			if (!field) {
				field = vdMetaPane.contentWindow.document.getElementById(fieldId);
			}
			if (field) {
				var cIndex = arChangeRegistry[fieldName+objectId];
				if (cIndex != null) {
					var cField = arChangeRegistry[cIndex];
					if (cField && arFieldRegistry[fieldId] != cField) {
						// Field indexed on objectId+name is not the same.
						// Most probably because this is a replacement (done via javascript)
						delete arChangeRegistry[ cIndex ];
						delete arChangeRegistry[new String(fieldName+objectId)];
					}
				}
				if (arChangeRegistry[fieldName+objectId]==null) {
					var startContent=field.startContent;
					var index=arChangeRegistry.length;
					arChangeRegistry[index]=arFieldRegistry[fieldId];
					arChangeRegistry[new String(fieldName+objectId)]=index;
				}
			}
		}
		if (!stoprecurse && arFieldRegistry[fieldId] && arFieldRegistry[fieldId].group) {
			groupList=arGroupRegistry[arFieldRegistry[fieldId].group];
			if (groupList) {
				for (var i=0; i<groupList.length; i++) {
					registerChange(groupList[i], true);
				}
			}
		}
	}

	function vdStoreUndo() {
		if (vdUndoEnabled) {
			var field=getEditableField();
			if (field) {
				var content=getValue(field.id)
				if (vdUndoHandler.store(field.id, content) && (content!=field.startContent)) {
					// content!=startContent is a defensive check to make sure the change is real
					registerChange(field.id);
				}
			}
		}
	}

	function navigateTo(newLocation) {
		// reloads full editor window
		if (isDirty()) {
			if ( doConfirmSave() ) {
				SAVE_onclick();
			} else {
				clearDirty(); // prevent onbeforeunload to ask again, since you already declined saving in doConfirmSave
			}
		}
		window.location=newLocation;
	}

	function browseTo(newPage) {
		// reloads only the editor pane, not editor toolbar/context menu
		if (isDirty()) {
			if ( doConfirmSave() ) {
				SAVE_onclick();
			} else {
				clearDirty(); // prevent onbeforeunload to ask again, since you already declined saving in doConfirmSave
			}
		}
		vdEditPane.contentWindow.location=newPage;
	}

	function checkDblClick(evt) {
		evt = muze.event.get(evt);
		var target = muze.event.target(evt);
		if( target ) {

			if( target.tagName != 'A' ) {
				target = target.parentNode;
			}

			if( target && target.tagName == 'A' ) {
				var arType = target.getAttribute('ar:type');
				if (arType && arType == 'internal') {
					var newLocation = target.getAttribute('href') + '<?php echo $wgHTMLEditTemplate.$getargs; ?>';
					if (isDirty() && doConfirmSave()) {
						var newLocation = target.getAttribute('href') + '<?php echo $wgHTMLEditManageTemplate.$getargs; ?>';
						SAVE_onclick(newLocation);
					} else {
						if ( isDirty() ) {
							clearDirty(); // prevent onbeforeunload to ask again, since you already declined saving in doConfirmSave
						}
						vdEditPane.contentWindow.document.location=newLocation;
					}
				}
			}
		}
		return false;
	}

	function initEditable() {
		var editable;

		var brokenWebkit = false;
		if( window.getSelection ) {
			var selection = vdEditPane.contentWindow.document.defaultView.getSelection();
			if( selection && selection.setBaseAndExtent ) { // broken webkit
				brokenWebkit = true;
			}
		}

		var all = vdEditPane.contentWindow.document.querySelectorAll(".editable");
		for(var k=0, all; elm=all[k++];) {
			editable = elm;

			registerDataField(elm.id, elm.getAttribute("data-vedor-field"), elm.getAttribute("data-vedor-path"), elm.getAttribute("data-vedor-id"));
			if ( vdDebug ) {
				 console.log("registered " + elm.id + ":" + elm.getAttribute("data-vedor-field") +":"+ elm.getAttribute("data-vedor-path") +":"+ elm.getAttribute("data-vedor-id"));
			}

			muze.event.attach(editable, 'focus', checkChangeStart);
			muze.event.attach(editable, 'blur', checkChangeEnd); // Blur is written here in lowercase, in this case firefox only supports lowercase!
			if (vdHandles) {
				muze.event.attach(editable, 'scroll', vdHandles.show);
			}

			if ( editable.tagName.toLowerCase()=='input' || editable.tagName.toLowerCase() == 'select') {
				muze.event.attach(editable, 'change', checkChangeEnd);
			} else {
				editable.contentEditable=true;
			}
			muze.event.attach(editable, 'dblclick', checkDblClick);
			if( brokenWebkit ) {
				muze.event.attach(editable, 'click', vdHandleBrokenWebkitSelect);
			}
		}
	}

	function initMeta() {
		var editable;

		var brokenWebkit = false;
		if( window.getSelection ) {
			var selection = document.getElementById("vdMetaFrame").contentWindow.document.defaultView.getSelection();
			if( selection && selection.setBaseAndExtent ) { // broken webkit
				brokenWebkit = true;
			}
		}

		var all = document.getElementById("vdMetaFrame").contentWindow.document.querySelectorAll(".editable");
		for(var k=0, all; elm=all[k++];) {
			editable = elm;

			registerDataField(elm.id, elm.getAttribute("data-vedor-field"), elm.getAttribute("data-vedor-path"), elm.getAttribute("data-vedor-id"));
			if (vdDebug) {
				 console.log("registered " + elm.id + ":" + elm.getAttribute("data-vedor-field") +":"+ elm.getAttribute("data-vedor-path") +":"+ elm.getAttribute("data-vedor-id"));
			}
			muze.event.attach(editable, 'focus', checkChangeStart);
			muze.event.attach(editable, 'blur', checkChangeEnd); // Blur is written here in lowercase, in this case firefox only supports lowercase!
			muze.event.attach(editable, 'mousedown', function() {
				vdStoreUndo();
				checkUndoRedo();
			});
			if (vdHandles) {
				muze.event.attach(editable, 'scroll', vdHandles.show);
			}

			if ( editable.tagName.toLowerCase()=='input' || editable.tagName.toLowerCase() == 'select') {
				muze.event.attach(editable, 'change', checkChangeEnd);
			} else {
				editable.contentEditable=true;
			}
			muze.event.attach(editable, 'dblclick', checkDblClick);
			if( brokenWebkit ) {
				muze.event.attach(editable, 'click', vdHandleBrokenWebkitSelect);
			}
		}

		muze.event.fire(document.getElementById("vdMetaFrame"), "initEditablePage");
	}

	var vdMetaTab = null;

	function vdSetMetaTab(name) {
		vdMetaTab = name;
	}

	function vdGetMetaTab() {
		return vdMetaTab;
	}


	function vdDisableButton(button) {
		var buttons = document.querySelectorAll("#" + button + ", button[data-vedor-action='" + button + "']");
		for (var i=0; i<buttons.length; i++) {
			buttons[i].classList.add("vedor-disabled");
		}
	}

	function vdEnableButton(button) {
		var buttons = document.querySelectorAll("#" + button + ", button[data-vedor-action='" + button + "']");
		for (var i=0; i<buttons.length; i++) {
			buttons[i].classList.remove("vedor-disabled");
		}
	}

	function checkUndoRedo() {
		if (vdUndoHandler.checkUndo()) {
			vdEnableButton('VD_UNDO');
		} else {
			vdDisableButton('VD_UNDO');
		}
		if (vdUndoHandler.checkRedo()) {
			vdEnableButton('VD_REDO');
		} else {
			vdDisableButton('VD_REDO');
		}
	}

	function getContainerPath(el) {
		var parent=el.parentNode;
		var path='';
		while (parent && parent.tagName!='BODY') {
			try {
				var type=parent.getAttribute('ar:type');
				if (type=='container') {
					path=parent.getAttribute('ar:path');
					break;
				}
			} catch(e) {
			}
			parent=parent.parentNode;
		}
		return path;
	}

	function checkChangeStart(evt) {
		var el = muze.event.target(evt);
		checkChangeStartEl(el);
		var startContent=el.startContent;
		if (startContent!==null && vdUndoEnabled) { //&& !el.undoStored
			vdUndoHandler.store(el.id,startContent);
			// el.undoStored=true;
		}
		// remove the current selection from our stored state and call display changed
		// these 2 are mainly called for firefox and co, due to not listening to unselectable = on
		vdSelectionState.remove();
		vdEditPane_DisplayChanged();
	}

	function checkChangeStartEl(el) {
		checkUndoRedo();
		el.startContent=getValue(el.id);
		currentEditableField=el;
	}

	function checkChangeEnd(evt) {
		var el = muze.event.target(evt);
/*
		var endContent=getValue(el.id);
		if (endContent!=el.startContent && vdUndoEnabled) {
			vdUndoHandler.store(el.id, endContent);
		}
*/

		//vedor.editor.compose.stop();
		checkChangeEndEl(el);
	}

	function checkChangeEndEl(el) {
		var newValue = getValue(el.id);
		if ((el.startContent!=newValue) && arFieldRegistry[el.id]) {
			registerChange(el.id);
			if (vdUndoEnabled) {
				vdUndoHandler.store(el.id, newValue);
			}

			var objectId = arFieldRegistry[el.id].id;
			for (var i in arObjectRegistry[objectId][arFieldRegistry[el.id].name]) {
				var fieldId = arObjectRegistry[objectId][arFieldRegistry[el.id].name][i].fieldId;
				arFieldRegistry[fieldId].value = newValue;
				if (fieldId!=el.id) {
					// don't update the content of the current field, since that breaks
					// selections.
					setValue(fieldId, newValue);
				}
			}
			el.startContent=newValue;
		}
	}

	function isDirty() {
		if (currentEditableField) {
			checkChangeEndEl(currentEditableField);
		}
		return arChangeRegistry.length;
	}

	function clearDirty() {
		arChangeRegistry=new Array();
	}

	function isEditable() {
		var field = getEditableField();
		return ( field ? true : false);
	}

	function getEditableField() {
		var vdParent=false;
		var sel = vdSelectionState.get();
		if (sel) {
			vdParent = vdSelection.getNode(sel);
			while(vdParent) {
				if (vdParent.classList && vdParent.classList.contains('editable')) {
					return vdParent;
				} else {
					vdParent = vdParent.parentNode;
				}
			}
			return false;
		}
		return false;
	}

	function restoreField(fieldId, fieldContents) {
		var result = false;
		if (fieldId && arFieldRegistry[fieldId]) {
			var objectId = arFieldRegistry[fieldId].id;
			for (var i in arObjectRegistry[objectId][arFieldRegistry[fieldId].name]) {
				var tempId = arObjectRegistry[objectId][arFieldRegistry[fieldId].name][i].fieldId;
				if (arFieldRegistry[tempId].value != fieldContents) {
					arFieldRegistry[tempId].value = fieldContents;
					setValue(fieldId, fieldContents);
					result = true;
				}
			}
		}
		return result;
	}

	function getDirtyField() {
		return arChangeRegistry.pop();
	}

	function VD_SITEMAP_onclick() {
		var args=new Array();
		args['path']=objectPath;
		var lang='<?php echo $language; ?>';
		var startpath=vdBrowseRoot;
		var arr = Array();
		var url = objectURL + 'dialog.browse.php<?php echo $getargs; ?>&root='+escape(startpath) + '&pathmode=siterelative';
		muze.dialog.open( url, 'sitemap', { windowFeatures :  muze.ariadne.explore.windowprops['dialog_browse'] } )
		.on('submit', function( arr ) {
			var ajax=getAjaxRequest();
			ajax.open("get", objectURL+"vd.hyperlink.makeurl.ajax?linkpath="+escape(arr['path']), false);
			ajax.send(arguments);
			if (arr['path'].substr(0, sitePath.length) != sitePath) {
				window.location = ajax.responseText + wgManageTmpl;
			} else {
				var newlocation = ajax.responseText;
				vdEditPane.src=newlocation+'<?php echo $wgHTMLEditTemplate.$getargs; ?>';
			}
		})
		.always( function() {
			this.close();
		});
	}

	var showBorders=true; //false;
	var showTagStack=true;

	function cssSetStyle( styleSheet, style, value ) {
		if( styleSheet.insertRule ) {
			// FIXME: hack to insert rule after @import, if set, needs better check
			// or better: append rule at the end - but avoid the length property
			try {
				styleSheet.insertRule( style + '{ ' + value + ' } ', 0);
			} catch(e) {
				try {
					styleSheet.insertRule( style + '{ ' + value + ' } ', 1);
				} catch(e) {
					//ignore
				}
			}
		} else { // IE
			styleSheet.addRule(style, value);
		}
	}

	function addBordersStyleSheet(doc) {
		var head = doc.getElementsByTagName('head')[0];
		var myStyle  = doc.createElement('link');
		myStyle.id   = "vedorBorders"
		myStyle.rel  = 'stylesheet';
		myStyle.href = '<?php echo $AR->dir->www; ?>widgets/vedor/borders.css';
		var head = doc.getElementsByTagName('HEAD')[0];
		head.insertBefore(myStyle, head.firstChild); // always insert as first stylesheet, so other stylesheets may override it			
	};

	function VD_DETAILS_onclick(borders) {
		if (borders===false) {
			showBorders=true;
		} else if (borders===true) {
			showBorders=false;
		}
		var vdEditDoc=vdEditPane.contentWindow.document;
		if (showBorders) {
			showBorders=false;
			if (document.getElementById("VD_DETAILS")) {
				document.getElementById('VD_DETAILS').classList.remove('vedor-selected');
			}
			vdEditDoc.body.classList.remove('vedor-borders');
		} else {
			showBorders=true;
			if (document.getElementById("VD_DETAILS")) {
				document.getElementById('VD_DETAILS').classList.add('vedor-selected');
			}
			vdEditDoc.body.classList.add('vedor-borders');
		}
		storeOption('borders', showBorders);
		if (document.getElementById("VD_DETAILS")) {
			if ( showBorders ) {
				document.getElementById('VD_DETAILS').classList.add('vedor-selected');
			} else {
				document.getElementById('VD_DETAILS').classList.remove('vedor-selected');
			}
		}
	}

	var showTagBoundaries = false;

	function showTagBoundariesToggle() {
		var vdEditDoc=vdEditPane.contentWindow.document;
		if (showTagBoundaries) {
			showTagBoundaries = false;
			vdEditDoc.body.classList.remove('vedor-tags');
			document.getElementById("vdShowTagBoundaries") ? document.getElementById("vdShowTagBoundaries").classList.remove('vedor-selected') : '';
		} else {
			showTagBoundaries = true;
			vdEditDoc.body.classList.add('vedor-tags');
			document.getElementById("vdShowTagBoundaries") ? document.getElementById("vdShowTagBoundaries").classList.add("vedor-selected") : '';
		}
		storeOption('tagBoundaries', showTagBoundaries);
	}

	function skipShowTag(tag) {
		if (tag.match(/^(?:area|br|col|embed|hr|img|input|link|meta|param|tr)$/i)) {
			return true;
		}
		return false;
	}

	function VD_META_onclick() {
		if (vdMetaDataSlideEnabled) {
			document.getElementById('vdMetaDataSlide').style.display='none';
			document.getElementById('vdMetaDataSlider').style.display='none';
			vdMetaDataSlideEnabled = false;
			document.getElementById('VD_META').classList.remove('vedor-selected');
			document.body.classList.remove("vedor-properties");
			storeOption('properties', false);
		} else {
			document.getElementById('vdMetaDataSlide').style.display='';
			document.getElementById('vdMetaDataSlider').style.display='';
			vdMetaDataSlideEnabled = true;
			document.getElementById('VD_META').classList.add('vedor-selected');
			document.body.classList.add("vedor-properties");
			storeOption('properties', true);
		}
		window_onresize();
	}

	var vdHtmlContextStack	= new Array();
	var tableStackIndex		= -1;
	var imgStackIndex		= -1;
	var htmlblockStackIndex	= -1;
	var currentImage		= null;
	var currentHTMLBlock	= null;

	function isMenuItem(el) {
		// best guess to check if we are in a menu-item;
		if (!el.getAttribute("data-vedor-path")) {
			return false;
		}
		if (el.parentNode.tagName != "A") {
			return false;
		}
		if (!el.parentNode.getAttribute("ondblclick").match(/browseTo/)) {
			return false;
		}
		var parent = el.parentNode;
		while (parent) {
			if (parent.classList.contains('menu')) {
				return true;
			}
			parent = parent.parentNode;
		}
		return false;
	}

	function parents(target) {
		var result = [];
		// while(target && (target.nodeType == 1) && !target.classList.contains("editable")) {
		while(target && (target.nodeType == 1)) {
			result.push(target);
			target = target.parentNode;
		}

		return result;
	}

	function checkVedorContext(filter, targets) {
		var sel = vdSelectionState.get();

		while (target = targets.shift()) {
			var tempNode = document.createElement("DIV");
			tempNode.appendChild(target.cloneNode(false));
			var result = 0;
			if (
				( (typeof filter["selector"] !== 'undefined') ? tempNode.querySelectorAll(":scope > " + filter["selector"]).length : true) && 
				( (typeof filter["sel-collapsed"] !== 'undefined') ? (sel.collapsed == filter["sel-collapsed"]) : true)
			) {
				result += 50 * (targets.length+1); // tagName weight
				if (typeof filter["selector"] !== 'undefined') {
					result += 2*(filter["selector"].split(".").length-1); // Add the number of class selectors;
					result += 2*(filter["selector"].split("[").length-1); // Add the number of attribute selectors
				}

				if (typeof filter["sel-collapsed"] !== 'undefined') {
					result += 1;
				}
				if (typeof filter["parent"] == 'undefined') {
					return result;
				} else {
					var parentResult = checkVedorContext(filter["parent"], targets);
					if (parentResult) {
						return result + parentResult;
					} else {
						return 0;
					}
				}
			}
		}
		return 0;
	}

	function getVedorEditorContext() {
		var sel = vdSelectionState ? vdSelectionState.get() : false;

		if (sel) {
			var parent = vdSelection.getNode(sel);

			if ((parent && parent.getAttribute && (parent.getAttribute("contenteditable") || parent.getAttribute("data-vedor-selectable"))) || hasEditableParent(parent)) {
				if (parent || parent.getAttribute || parent.getAttribute("contenteditable")) {
					var validFilters = {};
					var bestFilter = false;
					var bestFilterWeight = 0;
					for (var i in vedor.editor.contextFilters) {
						var filter = vedor.editor.contextFilters[i];
						var filterWeight = checkVedorContext(filter, parents(parent));

						if (filterWeight) {
							validFilters[i] = filterWeight;
							if (filterWeight > bestFilterWeight) {
								bestFilter = filter.context;
								bestFilterWeight = filterWeight;
							}
						}
					}
					return bestFilter;
				} else {
					if (sel.collapsed) {
						return "vedor-text-cursor";
					} else {
						return "vedor-text-selection";
					}
				}
			} else {
				return "vedor-no-context";
			}
		}
	}

	function initContextProperties(context) {
		if (vedor.editor.toolbars[context] && vedor.editor.toolbars[context].update) {
			return vedor.editor.toolbars[context].update();
		}


		// FIXME: Deze wordt nog niet gebruikt;
		switch (context) {
			case "vedor-text-selection" :
			case "vedor-table-cell-selection":
				vdHideToolbars = false;
				initTextProperties();
			case "vedor-image" :
				vdHideToolbars = false;
			// 	FIXME: Deze wordt nu nog in updateHtmlContext gedaan;
			//	initImageProperties();
			break;
			case "vedor-no-context" :
			break;
			case "vedor-hyperlink" :
				vdHideToolbars = false;
				initHyperlinkProperties();
			break;
			default:
			break;
		}

	}

	function getAllStyles(elem) {
		var styleNode = {};
		if (!elem) return []; // Element does not exist, empty list.
		if (elem == document) { return []; } // Document is not what we are looking for;

		var win = document.defaultView || window, style, styleNode = [];
		if (win.getComputedStyle) { /* Modern browsers */
			style = win.getComputedStyle(elem, '');
			for (var i=0; i<style.length; i++) {
				styleNode[style[i]] = style.getPropertyValue(style[i]);
				//			   ^name ^		   ^ value ^
			}
		} else if (elem.currentStyle) { /* IE */
			style = elem.currentStyle;
			for (var name in currentStyle) {
				styleNode[name] = currentStyle[name];
			}
		} else { /* Ancient browser..*/
			style = elem.style;
			for (var i=0; i<style.length; i++) {
				styleNode[style[i]] = style[style[i]];
			}
		}
		return styleNode;
	}

	function initHyperlinkProperties() {
		var sel = vdSelectionState.get();
		if (sel) {
			var parent = vdSelection.getNode(sel);
			if (parent || parent.getAttribute || parent.getAttribute("contenteditable")) {
				var hyperlinkNofollow = document.querySelectorAll("button.vedor-hyperlink-nofollow");
				for (var i=0; i<hyperlinkNofollow.length; i++) {
					if (parent.getAttribute("rel") && parent.getAttribute("rel").match(/nofollow/)) {
						hyperlinkNofollow[i].classList.add("vedor-selected");
					} else {
						hyperlinkNofollow[i].classList.remove("vedor-selected");
					}
				}
				var hyperlinkTitle = document.getElementById("vdHyperlinkTitle");
				hyperlinkTitle.value = parent.getAttribute("title");
			}
		}
	}

	function selectStyle(select, node) {
		for ( var i=0, l=select.options.length; i<l; i++ ) {
			var val = select.options[i].value;
			var info = val.split('.');
			var tag = info[0];
			var className = info[1];
			var n = node;
			while ( n && n.classList && !n.classList.contains('editable') ) {
				if ( n.tagName == tag ) {
					if ( !className || node.classList.contains(className) ) {
						select.options[i].selected = true;
						return n;
					}
				}
				n = n.parentElement;
			}
		}
	}

	function initTextProperties() {

		var sel = vdSelectionState.get();
		if (sel) {
			var parent = vdSelection.getNode(sel);
			if (parent || parent.getAttribute || parent.getAttribute("contenteditable")) {
				var parentStyles = getAllStyles(parent);
				var textFormat = document.querySelectorAll(".vedor-text-format select");
				for ( var i=0, l=textFormat.length; i<l; i++ ) {
					selectStyle(textFormat[i], parent);
				}

				var textAlign = document.querySelectorAll(".vedor-text-align[data-type=vedor-buttongroup-radio]");
				for (var i=0; i<textAlign.length;i++) {
					var parentStyles = getAllStyles(parent);

					switch (parentStyles["text-align"]) {
						case "right" :
							vdSetProperty(textAlign[i], "right");
						break;
						case "center" :
							vdSetProperty(textAlign[i], "center");
						break;
						case "justify" :
							vdSetProperty(textAlign[i], "justify");
						break;
						case "left" :
						default :
							vdSetProperty(textAlign[i], "left");
						break;
					}
				}

				// Set the parent icon for alignment as well;
				var currentIcon = document.querySelectorAll("div.vedor-text-align button.vedor-selected i")[0];
				var icons = document.querySelectorAll("button[data-vedor-section=vedor-text-align] i");
				for (var i=0; i<icons.length; i++) {
					icons[i].className = currentIcon.className;
				}

				// Check "Bold"
				var textBold = document.querySelectorAll(".vedor-text-bold button");
				for (var i=0; i<textBold.length; i++) {
					if (parentStyles["font-weight"] == "bold") {
						textBold[i].classList.add("vedor-selected");
					} else {
						textBold[i].classList.remove("vedor-selected");
					}
				}

				// Check "Italic"
				var textItalic = document.querySelectorAll(".vedor-text-italic button");
				for (var i=0; i<textItalic.length; i++) {
					if (parentStyles["font-style"] == "italic") {
						textItalic[i].classList.add("vedor-selected");
					} else {
						textItalic[i].classList.remove("vedor-selected");
					}
				}

				// Check "Underline"
				var textUnderline = document.querySelectorAll(".vedor-text-underline button");
				for (var i=0; i<textUnderline.length; i++) {
					if (parentStyles["text-decoration"].match(/underline/)) {
						textUnderline[i].classList.add("vedor-selected");
					} else {
						textUnderline[i].classList.remove("vedor-selected");
					}
				}
			}
		}
		return true;
	}

	var vdHideToolbars = false;
	var toolbarTimer = false;
	var skipContextUpdate = false;

	function getToolbarPosition(sel) {
		var range = sel; //.getRangeAt(0);
		if ( !range ) {
			return null;
		}
		var rects = range.getClientRects();
		var parent = vdSelection.getNode(sel);
		if ( !rects.length ) {
			// insert element at range and get its position, other options aren't exact enough
			var span = vdEditPane.contentDocument.createElement('span');
			if ( span.getClientRects ) {
				// Ensure span has dimensions and position by
				// adding a zero-width space character
				try {
					span.appendChild( vdEditPane.contentDocument.createTextNode("\u200b") );
					range.insertNode(span);
					rects = span.getClientRects();
					var spanParent = span.parentNode;
					spanParent.removeChild(span);
					// Glue any broken text nodes back together
					spanParent.normalize();
				} catch(e) {
				}
			}
		}
		if ( rects.length ) {
			var ltop = rects[0].top;
			var lleft = rects[0].left;
			var rleft = rects[rects.length-1].right;
			var rtop = rects[rects.length-1].bottom; 
		}
		if ( !rects.length || parent.getAttribute("data-vedor-selectable") ) {
			pos = parent && parent.getBoundingClientRect ? parent.getBoundingClientRect() : { left: 0, top: 0, right: 0, bottom: 0};
			lleft = pos.left;
			ltop = pos.top;
			rleft = pos.right;
			rtop = pos.bottom;
		}
		// fallback... if nothing else works
		if ( lleft == 0 && rleft == 0 && ltop == 0 && rtop == 0 ) {
			parent = vdSelection.parentNode(sel);
			if ( !parent || !parent.getBoundingClientRect ) {
				return false;
			}
			pos = parent.getBoundingClientRect();
			lleft = pos.left;
			ltop = pos.top;
			rleft = pos.right;
			rtop = pos.bottom;
		}
		var top = Math.max(ltop, rtop);
		var left = lleft + ((rleft - lleft) / 2);
		return { top: top, left: left, ltop: ltop, lleft: lleft, rtop: rtop, rleft: rleft };
	}

	function repositionToolbar() {
		var sel = vdSelectionState.get();
		if ( !currentContext ) {
			currentContext = getVedorEditorContext();
		}
		var activeSection = document.getElementById(currentContext);
		var pos = getToolbarPosition(sel);
		if ( !pos || !activeSection ) {
			hideToolbar(true);
			return;
		}
		var top = pos.top;
		var left = pos.left;
			
		var activeToolbar = activeSection.querySelectorAll("div.vedor-toolbar")[0];

		top += vdEditPane.offsetTop;

		var newleft = left - (activeToolbar.offsetWidth/2);

		if (newleft < 0) {
			var markerLeft = activeToolbar.offsetWidth/2 + newleft;
			activeToolbar.getElementsByClassName("marker")[0].style.left = markerLeft+'px';
			newleft = 0;
		} else if (newleft + activeToolbar.offsetWidth > vdEditPane.offsetWidth) {
			var delta = newleft + activeToolbar.offsetWidth - vdEditPane.offsetWidth;
			markerLeft = activeToolbar.offsetWidth/2 + delta;
			activeToolbar.getElementsByClassName("marker")[0].style.left = markerLeft+'px';

			newleft = vdEditPane.offsetWidth - activeToolbar.offsetWidth;
		} else {
			activeToolbar.getElementsByClassName("marker")[0].style.left = "50%";
		}

		// Move the toolbar to beneath the top of the selection if the toolbar goes out of view;
		// check the position 
		// - if toolbar bottom <= editor pane bottom, no problem
		// - if toolbar can be repositioned, no problem
		// - if edit pane content can be scrolled down, no problem
		// - else: add space on the bottom so that you can scroll down
		var editPaneRect = vdEditPane.getBoundingClientRect();
		var toolbarRect = activeSection.getBoundingClientRect();
		if ( top + toolbarRect.height > editPaneRect.height ) {
			// toolbar extends beyond bottom edge if not repositioned
			var mintop = Math.min(pos.ltop, pos.rtop);
			if ( mintop + toolbarRect.height <= editPaneRect.height ) {
				// toolbar can be repositioned
				// FIXME: min top should be position of the cursor, not selection
				top = editPaneRect.height - toolbarRect.height;
			} else {
				top = mintop;
				var scrollHeight = Math.max(vdEditPane.contentDocument.body.scrollHeight, vdEditPane.contentDocument.body.clientHeight);
				var scrollTop    = Math.max(vdEditPane.contentDocument.body.scrollTop, vdEditPane.contentDocument.documentElement.scrollTop);
				if ( scrollTop >= (scrollHeight - vdEditPane.contentDocument.body.clientHeight - toolbarRect.height ) ) {
					// no more scroll space, so add it.
					vdEditPane.contentDocument.body.classList.add('vedor-footer-space');
				}
			}
		}
		if ( vdEditPane.contentDocument.body.classList.contains('vedor-footer-space') ) {
			var scrollHeight = Math.max(vdEditPane.contentDocument.body.scrollHeight, vdEditPane.contentDocument.body.clientHeight);
			var scrollTop    = Math.max(vdEditPane.contentDocument.body.scrollTop, vdEditPane.contentDocument.documentElement.scrollTop);
			if ( scrollTop < (scrollHeight - vdEditPane.contentDocument.body.clientHeight - toolbarRect.height - 132 )) {
				vdEditPane.contentDocument.body.classList.remove('vedor-footer-space');
			}
		}
		activeSection.style.top = top + 10 + "px"; // 10 is the height of the marker arrow
		activeSection.style.left = newleft + "px";
	}

	function hideToolbar(clearFooter) {
		var sections = document.querySelectorAll("section.vedor-section");
		for (var j=0; j<sections.length; j++) {
			if (!(sections[j].className.match(/active/))) {
				sections[j].style.left = "-10000px";
			}
		}
		if ( clearFooter && vdEditPane.contentDocument.body.classList.contains('vedor-footer-space') ) {
			vdEditPane.contentDocument.body.classList.remove('vedor-footer-space');
		}
	}

	var currentContext = '';
	function showVedorEditorContext() {
		currentContext = getVedorEditorContext();

		var sections = document.querySelectorAll("section.vedor-section");
		for (var i=0; i<sections.length; i++) {
			sections[i].classList.remove("active");
		}

		initContextProperties(currentContext);

		//window.setTimeout(hideIt, 200);

		var activeSection = document.getElementById(currentContext);
		if ( vdDebug ) {
			console.log(activeSection);
		}
		if (activeSection && !vdHideToolbars) {
				var htmlContext = activeSection.querySelectorAll("div.vedor-toolbar-status")[0];
				if ( htmlContext ) {
					htmlContext.classList.add("vedor-selected");
				}
				// activeSection.style.display = "block";
				activeSection.className += " active";
				hideToolbar(); // window.setTimeout(hideIt, 200);


				var sel = vdSelectionState.get();
				var parent = vdSelection.getNode(sel);
				if (parent == vdEditPane.contentWindow.document) {
					return;
				}

				repositionToolbar();

// FIXME: Android fix here
//				// restore selection triggers contextupdate, which triggers restore selection - this hopefully prevents that loop.
				skipContextUpdate = true;
				if (!sel.collapsed) {
				//	vdSelectionState.restore(sel); // // FIXME: This reverses the current selection, which causes problems selecting from right to left; Is it used at all?
				}
				window.setTimeout(function() { skipContextUpdate = false;}, 20);
		} else {
			hideToolbar(true);
		}

		if (document.getElementById("VD_DETAILS")) {
			if (showBorders) {
				document.getElementById('VD_DETAILS').classList.add('vedor-selected');
			} else {
				document.getElementById('VD_DETAILS').classList.remove('vedor-selected');
			}
		}

		if (document.getElementById('vdShowTagBoundaries')) {
			if (showTagBoundaries) {
				document.getElementById('vdShowTagBoundaries').classList.add('vedor-selected');
			} else {
				document.getElementById('vdShowTagBoundaries').classList.remove('vedor-selected');
			}
		}

		if (document.getElementById('vdShowTagStack')) {
			if (showTagStack) {
				document.getElementById('vdShowTagStack').classList.add('vedor-selected');
			} else {
				document.getElementById('vdShowTagStack').classList.remove('vedor-selected');
			}
		}
	}

	function setHtmlTagsContext(contextStack) {
		var vdHtmlContext = document.querySelectorAll("section.vedor-section div.vedor-context-html");
		for (var i=0; i<vdHtmlContext.length; i++) {
			vdHtmlContext[i].innerHTML = contextStack;
		}
	}

	function hasEditableParent(checkParent) {
		var parent = checkParent;
		while (parent && parent.parentNode) {
			if (parent.parentNode.className && parent.parentNode.className.match(/\beditable\b/)) {
				return true;
			}
			parent = parent.parentNode;
		}
		return false;
	}

	function getUneditableParent(checkParent) {
		var parent = checkParent;
		while (parent) {
			if (parent.classList && parent.classList.contains('uneditable')) {
				return parent;
			} else if (parent.classList && parent.classList.contains('editable')) {
				return false;
			}
			parent = parent.parentNode;
		}
		return false;
	}

	function updateHtmlContext() {
		// Check if the current selection is part of an uneditable thing, if so, move the selection to that parent;
		var sel = vdSelectionState.get();
		var parent = vdSelection.getNode(sel);
		// console.log(parent);
		var selParent = getUneditableParent(parent);
		if (selParent) {
			// console.log(selParent);
			// Selection if part of something uneditable
			sel.selectNode(selParent);
			sel.startContainer.ownerDocument.defaultView.getSelection().removeAllRanges();
			sel.startContainer.ownerDocument.defaultView.getSelection().addRange(sel);
			vdSelectionState.save(sel);
			sel.startContainer.ownerDocument.defaultView.getSelection().removeAllRanges()
		}

		showVedorEditorContext();

		var parent			= false;
		var sel				= vdSelectionState.get();
		var imgOptions		= false;
		var htmlblockOptions= false;
		var newContextStack	= new Array();


		if (sel) {
			parent = vdSelection.getNode(sel);

			var contextString=new String();
			while (parent && hasEditableParent(parent) && parent.parentNode) {
				if (!imgOptions && parent.tagName=='IMG') {
					imgOptions=true;
					currentImage=parent;
					imgStackIndex=newContextStack.length;
				}
				try {
					if (!htmlblockOptions && parent.getAttribute('ar:type')=='htmlblock') {
						var htmlblock_id = parent.getAttribute('ar:id');
						if (tbContentEditOptions['htmlblocks'][htmlblock_id]['context']) {
							// var context_tmpl = tbContentEditOptions['htmlblocks'][htmlblock_id]['context'];
							htmlblockStackIndex = newContextStack.length;
							currentHTMLBlock = parent;
							htmlblockOptions = true;
							if( window.getSelection ) { // FF and Co
								if( sel.collapsed ) {
									var selection = vdEditPane.contentWindow.document.defaultView.getSelection();
									if( selection.setBaseAndExtent ) { // broken webkit
										selection.setBaseAndExtent(currentHTMLBlock, 0, currentHTMLBlock, 1);
									} else {
										sel.selectNode( currentHTMLBlock );
										sel.startContainer.ownerDocument.defaultView.getSelection().removeAllRanges();
										sel.startContainer.ownerDocument.defaultView.getSelection().addRange(sel);
									}
								}
							} else {
								if (!sel.text) {
									var r = vdEditPane.contentWindow.document.body.createControlRange();
									r.add( currentHTMLBlock );
									r.select();
								}
							}
							//alert('htmlblock with context template found');
						}
					} else if ( parent.getAttribute('contentEditable') == 'false' ) {
						// uneditable content block
						currentHTMLBlock = parent;
						if( window.getSelection ) { // FF and Co
							if( sel.collapsed ) {
								var selection = vdEditPane.contentWindow.document.defaultView.getSelection();
								if( selection.setBaseAndExtent ) { // broken webkit
									selection.setBaseAndExtent(currentHTMLBlock, 0, currentHTMLBlock, 1);
								} else {
									sel.selectNode( currentHTMLBlock );
									sel.startContainer.ownerDocument.defaultView.getSelection().addRange(sel);
									sel.startContainer.ownerDocument.defaultView.getSelection().removeAllRanges();
								}
							}
						} else {
							if (!sel.text) {
								var r = vdEditPane.contentWindow.document.body.createControlRange();
								r.add( currentHTMLBlock );
								r.select();
							}
						}
					}
				} catch(e) {
				}

				newContextStack.push(parent);
				contextString='<li unselectable="on"><a class="tag" href="#" title="<?php echo $ARnls["vd.editor:selecttag"]; ?>" unselectable="on" onClick="showContextInfo('+(newContextStack.length-1)+',this);">'+parent.tagName +
				//	'<span unselectable="on" class="deltag" title="<?php echo $ARnls['vd.editor:removetag']; ?>" onClick="return vdDelTag('+(newContextStack.length-1)+')">X</span>' +
					'</a></li>'+contextString;
				parent=parent.parentNode;
			}
		}

		setHtmlTagsContext('<ul class="tagStack" unselectable="on"><li class="vedor-label"><?php echo $ARnls['vd.editor:htmlcontext']; ?></li>'+contextString+'</ul>');
		vdHtmlContextStack=newContextStack;

		if (htmlblockOptions) {
			var context_tmpl = tbContentEditOptions['htmlblocks'][htmlblock_id]['context'];
			var vdHTMLBlockProperties = document.getElementById('vdHTMLBlockProperties');
			vdHTMLBlockProperties.src = objectURL + context_tmpl;
		}

		if (imgOptions) {
			initImageProperties(currentImage);
		}
	}

	function clearHtmlContext() {
		var vdHtmlContext = document.querySelectorAll("section.vedor-section div.vedor-context-html");
		for (var i=0; i<vdHtmlContext; i++) {
			vdHtmlContext[i].innerHTML = '';
		}
		vdHtmlContextStack = null;
		if (vdTableDesigner) {
			vdTableDesigner.deselectCell();
			vdTableDesigner=null;
		}
		if (vdHandles) { // important: vdTableDesigner must be cleared first, or you may get javascript security errors
			vdHandles.hide();
		}
		if (vdSelectedTab=='vdTabTable') {
			vdSelectTab('vdTabText');
		}
	}

	function vdDelTag(stackId) {
		var tag=vdHtmlContextStack[stackId];
		if (tag && confirm('<?php echo $ARnls['vd.editor:confirm_removetag']; ?>'.replace(/\%s/, tag.tagName))) {
			try {
				// FIXME: fix deletion of TABLE/TBODY/THEAD/TH/TR/TD/LI/UL/OL etc
				if( vdEditPane.contentWindow.getSelection ) {
					var sel = vdEditPane.contentWindow.getSelection();
					sel.removeAllRanges();
					var range = vdEditPane.contentWindow.document.createRange();
					range.selectNode(tag);
					sel.addRange(range);
					var frag = vdEditPane.contentWindow.document.createDocumentFragment();
					for (var i=0; i < tag.childNodes.length; i++) {
						var node = tag.childNodes[i].cloneNode(true);
						frag.appendChild(node);
					}
					range.deleteContents();
					range.insertNode(frag);
				} else { // IE shortcut
					tag.outerHTML=tag.innerHTML;
				}
				updateHtmlContext();
			} catch(e) {
			}
		}
		vdHtmlContextStack[stackId] = null; // Moz fix mainly
		return false;
	}

	var previousTagElement;


	function showContextInfo(stackId, tagElement) {
		if (vdHtmlContextStack[stackId]) {
			if (previousTagElement) {
				previousTagElement.style.fontWeight='normal';
			}
			tagElement.style.fontWeight='bold';
			previousTagElement=tagElement;

			var range = vdSelectionState.get();
			vdSelection.selectNode(range, vdHtmlContextStack[stackId], true);
		}
		updateHtmlContext();
	}

	function VD_ABOUT_onclick() {
		args=new Array();
		showModalDialog( objectURL+"vedor.about.html",args,"font-family:Verdana; font-size:12; dialogWidth:480px; dialogHeight:312px; status:no; resizable: yes;");
	}

	var vdSelectedTab='vdTabText';

	function vdSelectTab(id) {
		if (id!=vdSelectedTab) {
			var tab=document.getElementById(vdSelectedTab);
			tab.className='vdTabPane';
			tab=document.getElementById(id);
			tab.className='vdTabPaneSelected';
			vdSelectedTab=id;
		}
	}

	function vdSelectStyle(id) {
		var textStyles = document.querySelectorAll("select[name=textStyle]");
		for (var i=0; i< textStyles.length; i++) {
			var options = textStyles[i].getElementsByTagName("OPTION");
			for (var j=0; j<options.length; j++) {
				if (options[j].value.toLowerCase() == id.toLowerCase()) {
					options[j].selected = true;
				}
			}
		}
	}

	function vdGetHTMLSourceSpan() {
		var parent=false;
		var sel = vdSelectionState.get();
		if (sel) {
			parent = vdSelection.getNode(sel);
			while (parent && (parent.tagName=='DIV' || parent.tagName=='SPAN') && parent.parentNode && parent.getAttribute('vd:type')!='html') {
				parent=parent.parentNode;
			}
			if (!parent || (parent.tagName!='DIV' && parent.tagName!='SPAN') || parent.getAttribute('vd:type')!='html') {
				parent=false;
			}
		}
		return parent;
	}

	function vdInsertHTMLSelectAll() {
		var element = getEditableField();
		var sel = vdSelectionState.get();
		vdSelection.selectNode(sel, element);
		vdInsertHTMLOpen();
	}

	function vdInsertHTMLOpen() {
		var field=getEditableField();
		var field = true;
		if (field) {
			var sel = vdSelectionState.get();
			vdSelectionState.save(sel);
			document.getElementById('vdInsertHTMLPopup').style.display='block';
			document.getElementById('vdInsertHTMLPopupBg').style.display='block';
			var span = vdGetHTMLSourceSpan();
			if (span) {
				var source = vedor.util.base64.decode(span.getAttribute('vd:source'));
				document.getElementById('vdInsertHTMLSource').value = source;
				document.getElementById('vdInsertHTMLEditable').checked=false;
			} else {
				var sel = vdSelectionState.get();
				var source = vdSelection.getHTMLText(sel);

				document.getElementById('vdInsertHTMLSource').value = source;
				if (source) {
					document.getElementById('vdInsertHTMLEditable').checked=true;
				}
			}
			document.getElementById('vdInsertHTMLSource').select();
			vdInsertHTMLToggle();
		}
	}

	function vdInsertHTMLToggle() {
		var maxheight = 236;
		var textareaHeight = maxheight;

		var editable = document.getElementById('vdInsertHTMLEditable').checked;
		if (editable) {
			document.getElementById('vdInsertHTMLWarning').style.display='block';
		} else {
			document.getElementById('vdInsertHTMLWarning').style.display='none';
		}

		textareaHeight = textareaHeight - parseInt(document.getElementById('vdInsertHTMLWarning').offsetHeight);
		textareaHeight = textareaHeight - parseInt(document.getElementById('vdCookieConsent').offsetHeight);

		document.getElementById('vdInsertHTMLSource').style.height= textareaHeight + 'px';
	}

	function vdInsertHTMLClose() {
		document.getElementById('vdInsertHTMLPopup').style.display='none';
		document.getElementById('vdInsertHTMLPopupBg').style.display='none';
		vdSelectionState.restore();
		vdSelectionState.remove();
		vdSelectionState.selectAll = false;
		vdSelectionState.selectedElement = false;
		vdEditPane_DisplayChanged();
	}

	function vdInsertHTML(source) {
		var tidySource = tidy(source);
		if (tidySource != source) {
			if (confirm("The HTML content does not seem valid and cannot be inserted. Correct it automatically?")) {
				document.getElementById('vdInsertHTMLSource').value = tidySource;
			}
			return;
		}

		var field=getEditableField();
		if (!field && vdSelectionState.selectAll && vdSelectionState.selectedElement) {
			field = vdSelectionState.selectedElement;
		}
		if (field) {
			var editable = (document.getElementById('vdInsertHTMLEditable').checked==true);
			if (!editable) {
				var cookieConsentRequired = (document.getElementById('vdCookieConsentRequired').checked == true);
				var ccRequired = '';
				if (cookieConsentRequired) {
					ccRequired = 'vd:cookieconsentrequired="true" ';
				}
				var encodedsource = vedor.util.base64.encode(source); // this makes sure IE doesn't touch the sourcecode.
				source = '<div class="uneditable" vd:type="html" vd:source="'+encodedsource+'" ' + ccRequired + 'contenteditable="false">'+source+'<span vd:endsource="true"></span></div>';
			}
			if (vdSelectionState.selectAll) {
				field.innerHTML = source;
			} else {
				var sel = vdSelectionState.get();
				if (sel) {
					if( window.getSelection ) {
						var selection = vdEditPane.contentWindow.document.defaultView.getSelection();
						if( selection.setBaseAndExtent ) { // broken webkit
							var node = sel.commonAncestorContainer.parentNode;
							if( node && node.contentEditable == "false" ) {
								sel.selectNode(node);
							}
						}
					}
					setFormat('delete'); // this is somehow needed to trick IE into breaking potential <P>'s into 2
					vdSelection.setHTMLText(sel, source);
				}
			}
		}
		vdSelectionState.selectAll = false;
		vdSelectionState.selectedElement = false;
		vdInsertHTMLClose();
	}

	function vdHelp(item) {
		var items = {
			'meta' : 'http://help.vedor.com/editor/meta/',
			'languages' : 'http://help.vedor.com/editor/languages/',
			'scripts' : 'http://help.vedor.com/editor/scripts/',
			'sourcecode' : 'http://help.vedor.com/editor/sourcecode/',
			'default' : 'http://help.vedor.com/editor/'
		}
		if (!item || !items[item]) {
			item='default';
		}
		var help=window.open(items[item]);
		help.focus();
	}

	function vdLogOff() {
		if (confirm('<?php echo $ARnls['vd.editor:logoff_question']; ?>')) {
			document.location=logoffURL+'logoff.php';
		}
	}

</script>
</head>
<body unselectable="on">
<div id="vdEditor" unselectable="on">
<div id="vdToolbars" unselectable="on">
<?php $this->call("toolbar.vedor-main-toolbar.html"); ?>
</div>
<div id="vdContextBar">
</div>
<div id="editorPane">
<div id="vdSavePopup">
	<i id="vdSaveImage" class="fa fa-spinner fa-spin"></i>
	<div id="vdSaveHeader"><?php echo $ARnls['vd.editor:saving_header']; ?></div>
	<div id="vdSaveMessage"><?php echo $ARnls['vd.editor:saving_message']; ?></div>
</div>
<div id="vdProgressPopup">
	<i class="fa fa-spinner fa-spin"></i>
	<div id="vdProgressHeader"><?php echo $ARnls['vd.editor:uploading_header']; ?></div>
	<progress id="vdProgress" class="uploadprogress" min="0" max="100" value="0">0</progress>
</div>
<div id="vdComposePopup" style="display: none;" unselectable="on"></div>
<div id="vdInsertPopup" style="display: none;" unselectable="on">
</div>
<iframe src="" id="vdInsertHTMLPopupBg"></iframe>
<div id="vdInsertHTMLPopup" style="display: none;">
	<div class="vdDialogBody">
		<div class="vdHeader">
			<a href="#" class="vdButtonLarge" style="display: block; float: right; color: black;" unselectable='on' ID="VD_SOURCE_HELP" TITLE="Link" LANGUAGE="javascript" onclick="return vdHelp('sourcecode')">
				<i class="fa fa-question-circle"></i>
				<?php echo$ARnls['vd.editor:help']; ?>
			</a>
			<label><input type="checkbox" class="vdCheckbox" name="vdInsertHTMLEditable" id="vdInsertHTMLEditable" value="1" onClick="vdInsertHTMLToggle()" onPropertyChange="vdInsertHTMLToggle()"> <?php echo $ARnls['vd.editor:source_editable']; ?></label>
			<div class="formField formCheckbox" id="vdCookieConsent"><label><input type="checkbox" class="vdCheckbox" name="vdCookieConsentRequired" id="vdCookieConsentRequired" value="1"><?php echo $ARnls['vd.editor:cookie_consent_required']; ?></label></div>
			<div class="vdYellowAlert" id="vdInsertHTMLWarning" style="display: none;"><?php echo str_replace('<a>', '<a href="#" onClick="return vdHelp(\'sourcecode\');">', sprintf($ARnls['vd.editor:source_warning'])); ?></div>
		</div>
		<div class="vdContent">
			<textarea id="vdInsertHTMLSource"></textarea>
		</div>
	</div>
	<div class="vdDialogButtons">
		<a href="#" class="vdDialogButton right" unselectable="on" id="vdInsertHTMLCancel" title="<?php echo $ARnls['vd.editor:cancel'];?>" onClick="vdInsertHTMLClose();"><?php echo $ARnls['vd.editor:cancel']; ?></a>
		<a href="#" class="vdDialogButton right" unselectable="on" id="vdInsertHTMLButton" title="<?php echo $ARnls['vd.editor:insert'];?>" onClick="vdInsertHTML(document.getElementById('vdInsertHTMLSource').value)"><?php echo $ARnls['vd.editor:insert'];?></a>
		<a href="#" class="vdDialogButton left" unselectable="on" id="vdInsertHTMLSelectAll" title="<?php echo $ARnls['vd.editor:cancel'];?>" onClick="vdInsertHTMLSelectAll();"><?php echo $ARnls['vd.editor:selectall']; ?></a>
	</div>
</div>
<div id="vdMetaDataSlide" style="height: 220px; display: none;" unselectable="on" style="display: none; -webkit-overflow-scrolling:touch; overflow: scroll;">
	<iframe id="vdMetaFrame" src="<?php echo $this->make_local_url(); ?>dialog.vedor-meta.html?vdLanguage=<?php echo RawUrlEncode($language); ?>" unselectable="on"></iframe>
</div>
<div id="vdMetaDataSlider" style="display: none;" unselectable="on"></div>
<iframe id="vdEditPane" src="<?php
	/* FIXME FIXME FIXME FIXME FIXME FIXME FIXME FIXME FIXME FIXME FIXME FIXME FIXME FIXME FIXME
		dit werkt tijdelijk om het probleem heen dat de editor niet gestart kan wordne als er geen default view is gedefinieerd
	*/
	$tmpshortcut_redirect = $ARCurrent->shortcut_redirect??null;
	$ARCurrent->shortcut_redirect = array();
	$wgVedorEditTemplateArguments = "vdLanguage=$language";
	if (strpos($wgHTMLEditTemplate, '?') !== false) {
		$wgVedorEditTemplateArguments = "&".$wgVedorEditTemplateArguments;
	} else {
		$wgVedorEditTemplateArguments = "?".$wgVedorEditTemplateArguments;
	}
	echo $this->make_local_url().$wgHTMLEditTemplate.$wgVedorEditTemplateArguments;
	$ARCurrent->shortcut_redirect = $tmpshortcut_redirect;
?>" unselectable="on"></iframe>
<script>
	preinit();
</script>
<script type="text/javascript">
	vedor.editor.actions = {
		"vedor-save" : function(el) {
			SAVE_onclick();
		},

		/* Manage page */
		"vedor-new" : function(el) {
			NEW_onclick();
		},
		"vedor-sitemap" : function(el) {
			VD_SITEMAP_onclick();
		},
		"vedor-copy" : function(el) {
			COPY_onclick();
		},
		"vedor-rename" : function(el) {
			RENAME_onclick();
		},
		"vedor-move" : function(el) {
			MOVE_onclick();
		},
		"vedor-delete" : function(el) {
			DELETE_onclick();
		},

		"vedor-undo" : function(el) {
			VD_UNDO_onclick();
		},
		"vedor-redo" : function(el) {
			VD_REDO_onclick();
		},
		"vedor-help" : function(el) {
			vdHelp();
		},
		"vedor-insert-source" : function(el) {
			vdInsertHTMLOpen();
		},
		/* Text style actions */
		"vedor-text-align-left" : function(el) {
			VD_JUSTIFYLEFT_onclick();
		},
		"vedor-text-align-right" : function(el) {
			VD_JUSTIFYRIGHT_onclick();
		},
		"vedor-text-align-center" : function(el) {
			VD_JUSTIFYCENTER_onclick();
		},
		"vedor-text-align-justify" : function(el) {
			VD_JUSTIFYFULL_onclick();
		},
		"vedor-listitem-indent" : function(el) {
			VD_INDENT_onclick();
		},
		"vedor-listitem-outdent" : function(el) {
			VD_OUTDENT_onclick();
		},
		"vedor-text-bold" : function(el) {
			VD_BOLD_onclick();
		},
		"vedor-text-italic" : function(el) {
			VD_ITALIC_onclick();
		},
		"vedor-text-underline" : function(el) {
			VD_UNDERLINE_onclick();
		},

		"vedor-menu-hide" : function(el) {
			vdMenuHide_onclick();
		},
		"vedor-menu-sort" : function(el) {
			vdMenuSort_onclick();
		},
		"vedor-hyperlink-insert" : function(el) {
			VD_HYPERLINK_onclick();
		},
		"vedor-follow-link" : function(el) {
			var oSel = vdSelectionState.get();
			var control = vdSelectionState.getControlNode(oSel);
			if (control) {
				oElement=control;
				oParent=oElement.parentNode;
			} else {
				if( oSel.select ) { // IE only
					var htmlText = vdSelection.getHTMLText(oSel);
					if (htmlText.substr(htmlText.length-4, 4)=='<BR>') {
						// BR included in selection as last element, remove it, it has
						// dangerous effects on the hyperlink command in IE
						oSel.moveEnd('character',-1);
						oSel.select();
					}
					if (htmlText.substr(0,4)=='<BR>') {
						// idem when its the first character
						oSel.moveStart('character',1);
						oSel.select();
					}
				}
				oParent = vdSelection.parentNode(oSel);
			}
			while ( oParent && oParent.tagName != 'A' ) {
				oParent = oParent.parentNode;
			}
			if ( oParent ) {
				var arType = oParent.getAttribute('ar:type');
				if (arType && arType == 'internal') {
					var newLocation = oParent.getAttribute('href') + '<?php echo $wgHTMLEditTemplate.$getargs; ?>';
					if (isDirty() && doConfirmSave()) {
						var newLocation = oParent.getAttribute('href') + '<?php echo $wgHTMLEditManageTemplate.$getargs; ?>';
						SAVE_onclick(newLocation);
					} else {
						if ( isDirty() ) {
							clearDirty(); // prevent onbeforeunload to ask again, since you already declined saving in doConfirmSave
						}
						vdEditPane.contentWindow.document.location=newLocation;
					}
				} else if ( arType && arType == 'external' ) {
					window.location.href = oParent.getAttribute('href');
				} else {
					muze.event.fire(oParent, 'dblclick');
				}
			}
		},
		"vedor-insert-image" : function(el) {
			VD_IMAGE_onclick();
		},
		"vedor-image-upload" : function(el) {
			if (currentImage) {
				var input, path, form;
				form = muze.html.el(
					'form', 
					{
						'method' : 'post',
						'action' : objectURL + '',
						'target' : '_blank' // temp
					}, 
					input = muze.html.el(
						'input', 
						{
							'type' : 'file',
							'name' : 'file'
						}
					),
					path = muze.html.el('input', { 'type' : 'hidden', 'name' : 'path' } )
				);
				
				var imagePath = currentImage.getAttribute("data-vedor-path");
				if (!imagePath) {
					imagePath = currentImage.getAttribute("ar:path");
				}

				path.value = imagePath;

				var onImageLoad = function() {
					var imgsrc = currentImage.src.replace(/[\?\&]t=.*$/, '');
					var query = imgsrc.indexOf('?');
					if ( query>=0 ) {
						imgsrc += '&t='+Math.random();
					} else {
						imgsrc += '?t='+Math.random();
					}
					currentImage.src = imgsrc;
				};

				var inputChange = muze.event.attach(input, 'change', function() {
					muze.event.detach(input, 'change', inputChange);
					if ( input.value ) {
						var formData = new FormData();
						for (var i = 0; i < this.files.length; i++) {
							formData.append('file[]', this.files[i]);
						}
						formData.append("filecount", this.files.length);
						formData.append("overwrite", true);
						formData.append("replace", true);

						var progress = document.getElementById("vdProgress");
						document.getElementById("vdProgressPopup").style.display = "block";
						var xhr = new XMLHttpRequest();
						xhr.open('POST', rootURL + imagePath + "mfu.save.html");
						xhr.onload = function() {
							progress.value = progress.innerHTML = 100;
							// progress.style.display = "none";
							document.getElementById("vdProgressPopup").style.display = "none";

							window.setTimeout(onImageLoad, 50);
						};

						xhr.upload.onprogress = function (event) {
							if (event.lengthComputable) {
								var complete = (event.loaded / event.total * 100 | 0);
								progress.value = progress.innerHTML = complete;
							}
						}

						xhr.send(formData);
					}
				});
				muze.event.fire(input, 'click');
			}
		},
		"vedor-image-browse" : function(el) {
			VD_IMAGE_onclick();
		},
		"vedor-insert-symbol" : function(el) {
			var composePopup = document.getElementById("vdComposePopup");
			el.appendChild(composePopup);
			VD_SYMBOL_onclick();
		},
		"vedor-image-editor" : function(el) {
			VD_IMAGE_EDIT_onclick();
		},
		"vedor-borders" : function(el) {
			VD_DETAILS_onclick();
			return false;
		},
		"vedor-show-tags" : function(el) {
			showTagBoundariesToggle();
			return false;
		},
		"vedor-show-tags-stack" : function(el) {
			var tagStackToolbars = document.querySelectorAll('.vedor-toolbar-status');
			showTagStack = !showTagStack;
			storeOption('tagStack', showTagStack);
			for ( var i=0,l=tagStackToolbars.length; i<l; i++ ) {
				if ( showTagStack ) {
					tagStackToolbars[i].classList.remove('vedor-hidden');
					el.classList.add('vedor-selected');
				} else {
					tagStackToolbars[i].classList.add('vedor-hidden');
					el.classList.remove('vedor-selected');
				}
			}
			window_onresize();
		},
		"vedor-properties" : function(el, enabled) {
			if ( vdMetaDataSlideEnabled != enabled ) {
				VD_META_onclick();
			}
			return false;
		},
		"vedor-insert-gadget" : function(el) {
			var gadgetPopup = document.getElementById("vdInsertPopup");
			// el.appendChild(gadgetPopup);
			vdToggleInsert();
			return false;
		},
		"vedor-edit-source" : function(el) {
			var sel = vdSelectionState.get();
			var parent = vdSelection.parentNode(sel);
			if (parent.getAttribute("contenteditable")) {
				vdSelection.select(sel, parent);
			} else {
				sel.selectNode(parent);
				vdSelection.select(sel);
			}
			vdSelectionState.save(sel);

			vdInsertHTMLOpen();
		},
		"vedor-hyperlink-nofollow" : function(el) {
			var sel = vdSelectionState.get();
			var parent = vdSelection.getNode(sel);
			var rel = parent.getAttribute("rel");
			if (!rel) {
				rel = '';
			}

			if (rel.match(/\bnofollow\b/)) {
				rel = rel.replace(/\bnofollow\b/, '');
				parent.setAttribute("rel", rel);
			} else {
				rel = rel + " nofollow";
				parent.setAttribute("rel", rel);
			}

			rel = rel.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
			if (rel) {
				parent.setAttribute("rel", rel);
			} else {
				parent.removeAttribute("rel");
			}
		},
		"vedor-clipboard-select-all" : function(el) {
			var sel = vdSelectionState.get();
			vdSelection.selectNode(sel, vdSelection.getNode(sel), true);
		},
		"vedor-image-align-left" : function(el) {
			vdSetProperty("vdImageAlign", "left");
			vdSetImage();
			return false;
		},
		"vedor-image-align-center" : function(el) {
			vdSetProperty("vdImageAlign", "center");
			vdSetImage();
			return false;
		},
		"vedor-image-align-right" : function(el) {
			vdSetProperty("vdImageAlign", "right");
			vdSetImage();
			return false;
		},
		"vedor-image-align-none" : function(el) {
			vdSetProperty("vdImageAlign", "none");
			vdSetImage();
			return false;
		},
		"vedor-image-delete" : function(el) {
			var sel = vdSelectionState.get();
			var image = vdSelection.getNode(sel);
			image.parentNode.removeChild(image);
		},
		"vedor-context" : function(el) {
			var vdContext = document.getElementById("vdContextBar");
			if (vdContext) {
				if (vdContext.className.match(/active/)) {
					vdContext.classList.remove('active');
					storeOption('contextBar', false);
				} else {
					vdContext.classList.add('active');
					storeOption('contextBar', true);
				}
			}
		},
		"vedor-switch-language" : function(el) {
			var newURL = el.dataset.vedorLink;
			browseTo(newURL);
		},
		"vedor-dock-toolbars" : function(el) {
			if ( document.body.classList.contains('vedor-toolbars-docked') ) {
				document.body.classList.remove('vedor-toolbars-docked');
				storeOption('dockToolbars', false);
				el.classList.remove('vedor-selected');
			} else {
				document.body.classList.add('vedor-toolbars-docked');
				storeOption('dockToolbars', true);
				el.classList.add('vedor-selected');
			}
		}
	};
	vedor.editor.toolbars = {};
	vedor.editor.contextFilters = {};

	vedor.editor.addToolbar = function(toolbar) {
		if (toolbar.filter) {
			vedor.editor.addContextFilter(toolbar.name, toolbar.filter);
		}
		for (i in toolbar.actions) {
			vedor.editor.actions[i] = toolbar.actions[i];
		}
		vedor.editor.toolbars[toolbar.name] = toolbar;
	}

	vedor.editor.addContextFilter = function(name, filter) {
		if (!filter['context']) {
			filter['context'] = name;
		}
		vedor.editor.contextFilters[name] = filter;
	}
</script>
<?php
	foreach ($options['editor-toolbars'] as $toolbar) {
		$this->call($toolbar);
	}
?>
<script type="text/javascript">
	(function() {
		function getToolbar(el) {
			while ( el && el.tagName!='div' && !/\bvedor-toolbar\b/.test(el.className) ) {
				el = el.parentNode;
			}
			return el;
		}

		function getSection(el) {
			while ( el && el.tagName!='div' && !/\bvedor-toolbar-section\b/.test(el.className) ) {
				el = el.parentNode;
			}
			return el;
		}

		var lastEl = null;
		var lastSection = document.querySelectorAll('.vedor-toolbar-status')[0];

		var initEditPaneEvents = function() {
			var scrollTimer = false;
			muze.event.attach(document.getElementById("vdEditPane").contentWindow, "scroll", muze.throttle(repositionToolbar, 10));
			muze.event.attach(document.getElementById("vdEditPane").contentWindow, "keydown", function(event) {
				var key = event.keyCode || event.which;
				if (key == 66 && event.ctrlKey) { // Ctrl-B
					VD_BOLD_onclick();
					muze.event.cancel(event);
				} else if (key == 27) { // ESC
					vdHideToolbars = true;
					updateHtmlContext();
					muze.event.cancel(event);
				} else if (key == 73 && event.ctrlKey) { // Ctrl-I
					VD_ITALIC_onclick();
					muze.event.cancel(event);
				} else if (key == 83 && event.ctrlKey) { // Ctrl-S
					SAVE_onclick();
					muze.event.cancel(event);
				} else if (key == 32 && event.ctrlKey) { // Ctrl-space
					vdHideToolbars = false;
					updateHtmlContext();
					var activeToolbar = document.querySelectorAll(".vedor-section.active")[0];
					if (activeToolbar) {
						var firstButton = activeToolbar.querySelectorAll("button")[0];
						if (firstButton) {
							firstButton.focus();
						}
					}

					var toolbarTarget = document.querySelectorAll(".vedor-section.active .vedor-buttons > li")[0];
					
					if (toolbarTarget) {
						toolbarTarget.focus();
					}
					muze.event.cancel(event);
				} else if (key == 77 && event.ctrlKey) { // Ctrl-M
					document.querySelector("#vedor-main-toolbar button").focus();
					muze.event.cancel(event);
				} else {
					vdHideToolbars = true;
				}
			});
		};

		muze.event.attach(window, "keydown", function(event) {
			var key = event.keyCode || event.which;
			if (key == 27) { // ESC
				vdHideToolbars = true;
				updateHtmlContext();
				muze.event.cancel(event);
			} else if (key == '77' && event.ctrlKey) { // ctrl-M
				document.querySelector("#vedor-main-toolbar button").focus(); 
				muze.event.cancel(event);
			}
		});

		var vedorSections = document.querySelectorAll(".vedor-section");
		for (var i=0; i<vedorSections.length; i++) {
			muze.event.attach(vedorSections[i], "keydown", function(event) {
				var key = event.keyCode || event.which;
				if (key == 27) { // ESC
					vdHideToolbars = true;
					updateHtmlContext();
					muze.event.cancel(event);
				} else if (key == 37) { // left
					var target = this.querySelectorAll(":focus")[0]; 
					var previousSibling = target.parentNode.previousSibling;
					while (previousSibling) {
						if (previousSibling.nodeType == 1) {
							if (
								previousSibling.childNodes[0] && 
								previousSibling.offsetWidth > 0 
							) {
								break;
							}
						}
						previousSibling = previousSibling.previousSibling;
					}

					if (previousSibling) {
						previousSibling.querySelector("*").focus();
					}
					muze.event.cancel(event);
				} else if (key == 38) { // up
					// close current toolbar section;
					var targets = this.querySelectorAll('.vedor-selected');
					for (var i=0; i<targets.length; i++) {
						targets[i].classList.remove("vedor-selected");
					}
					targets[0].focus();
					muze.event.cancel(event);
				} else if (key == 39) { // right
					var target = this.querySelectorAll(":focus")[0]; 
					var nextSibling = target.parentNode.nextSibling;
					while (nextSibling) {
						if (nextSibling.nodeType == 1) {
							if (nextSibling.childNodes[0] && nextSibling.offsetWidth > 0) {
								break;
							}
						}
						nextSibling = nextSibling.nextSibling;
					}

					if (nextSibling) {
						nextSibling.querySelector("*").focus();
					}
					muze.event.cancel(event);
				} else if (key == 40) { // down
					// close current toolbar section;
					var target = this.querySelector(':focus');
					if (target.classList.contains("vedor-expands")) {
						if (target.classList.contains("vedor-selected")) {
							muze.event.fire(target, "click");
						}
						muze.event.fire(target, "click");
						muze.event.cancel(event);
					}
				}
			});
		}

		document.getElementById("vdImageAlt") ? muze.event.attach(document.getElementById("vdImageAlt"), "change", vdSetImage) : false;
		document.getElementById("vdImageTitle") ? muze.event.attach(document.getElementById("vdImageTitle"), "change", vdSetImage) : false;
		document.getElementById("vdImageType") ? muze.event.attach(document.getElementById("vdImageType"), "change", vdSetImage) : false;
		document.getElementById("VD_NLS_SELECT") ? muze.event.attach(document.getElementById("VD_NLS_SELECT"), "change", VD_NLS_onclick) : false;

		if (document.getElementById("vdHyperlinkTitle")) {
			muze.event.attach(document.getElementById("vdHyperlinkTitle"), "change", function() {
				var sel = vdSelectionState.get();
				var parent = vdSelection.getNode(sel);
				parent.setAttribute("title", this.value);
			});
		}

		muze.form.keyboardNumbers.attach();

		var textStyles = document.querySelectorAll("select[name=textStyle]");
		for (var i=0; i<textStyles.length; i++) {
			muze.event.attach(textStyles[i], "change", function() { setFormatStyle(this.value); } );
		}

		var toolbars = document.querySelectorAll(".vedor-toolbar");
		for (var i=0; i<toolbars.length; i++) {
			var marker = document.createElement("div");
			marker.className = "marker";
			toolbars[i].insertBefore(marker, toolbars[i].firstChild);
		}


		muze.event.attach(vdEditPane, "load", function() {
			vdEditPane.contentWindow.document.body.focus();
			initEditPaneEvents();
			muze.event.attach(vdEditPane.contentWindow.document, "click", function(event) {
				if (vdHideToolbars) {
					vdHideToolbars = false;
					showVedorEditorContext();
				}
			});
			muze.event.attach(vdEditPane.contentWindow.document, "touchend", function() {
				vdHideToolbars = false;
				showVedorEditorContext();
			});
		});

		var lastSelection = false;
		muze.event.attach(document.body, "touchstart", function() {
			lastSelection = vdSelectionState.get();
		});

		function unselectItems(list) {
			for ( var i=0, l=list.length; i<l; i++ ) {
				list[i].classList.remove('vedor-selected');
			}
		}
		function selectItem(item) {
			item.classList.add('vedor-selected');
		}

		document.body.onclick = function(evt) {
			if (lastSelection) {
				vdSelectionState.restore(lastSelection);
			}

			var el = evt.target;
			if ( el.tagName=='I' ) {
				el = el.parentNode;
			}
			if ( el.tagName == 'BUTTON' ) {
				var action = el.dataset.vedorAction;
				if ( action ) { // find the matching callback function and run it
					var actionCallback = vedor.editor.actions[action];
					if (actionCallback) {
						var result = actionCallback(el);
						if (!result) {
							return;
						}
					} else {
						console.log(action + " not yet implemented");
					}
				}

				evt.target.blur();
				var toolbar = getToolbar(el);
				var section = getSection(el);
				if ( !section ) {
					var sections = toolbar.querySelectorAll('.vedor-toolbar-section.vedor-selected, .vedor-toolbar-status');
					unselectItems(sections);
					var selectedSectionButtons = toolbar.querySelectorAll('ul.vedor-buttons button.vedor-selected');
					unselectItems(selectedSectionButtons);
					if ( !selectedSectionButtons[0] || el != selectedSectionButtons[0] ) {
						selectItem(el);
						var rel = el.dataset.vedorSection;
						if ( rel ) {
							var target = toolbar.querySelector('.vedor-toolbar-section.' + rel );
							if ( target ) {
								selectItem(target);
								lastSection = target;
								lastSection.querySelector("LI > *").focus();
							}
						}
					} else {
						var status = toolbar.querySelector('.vedor-toolbar-status');
						if ( status ) {
							selectItem(status);
						}
					}
				} else {
					var selectedSectionButtons = section.querySelectorAll('.vedor-selected');
					unselectItems(selectedSectionButtons);
					if ( !selectedSectionButtons[0] || el != selectedSectionButtons[0] ) {
						selectItem(el);
					}
				}
				window_onresize();
			}
		}
	})();
</script>
</div></div>
</body>
</html>
