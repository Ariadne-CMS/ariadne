<?php
	$ARCurrent->nolangcheck=true;
	$proceed = false;

	if ($path == $this->path) {
		if ((($this->arIsNewObject &&
				($parentobj=current($this->get($this->parent, "system.get.phtml"))) &&
				$parentobj->CheckLogin("add", $this->type)) ||
				(!$this->arIsNewObject && $this->CheckLogin("edit", $this->type)) )
				&& $this->CheckConfig()) {

			$proceed = true;
		}
	} else {
		if ($this->exists($path)) {
			$target_object = current($this->get($path, "system.get.phtml"));
			if ($target_object->CheckLogin("edit", $target_object->type) && $this->CheckConfig()) {
				$proceed = true;
			}
		} else {
			$target_object = current($this->get(
								$this->store->make_path($path, ".."),
								"system.get.phtml"));
			if ($this->CheckConfig()) {
				$proceed = true;
			}
		}
	}


	if ($proceed) {
		if ( $AR->user->data->editor == "toolbar") {
			$wgHTMLEditTemplate="edit.object.html.page.phtml";
			include($this->store->get_config("code")."widgets/htmledit/toolbar.php");
		} else {
			if ($file) {
				$file.="/";
			}
			$yui_base = $AR->dir->www . "js/yui/";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Ariadne HTML editor</title>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base; ?>menu/assets/skins/sam/menu.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base; ?>button/assets/skins/sam/button.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base; ?>fonts/fonts-min.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base; ?>container/assets/skins/sam/container.css">
<link rel="stylesheet" type="text/css" href="<?php echo $yui_base; ?>editor/assets/skins/sam/editor.css">
<script type="text/javascript" src="<?php echo $yui_base; ?>yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="<?php echo $yui_base; ?>element/element-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base; ?>container/container-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base; ?>menu/menu-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base; ?>button/button-min.js"></script>
<script type="text/javascript" src="<?php echo $yui_base; ?>editor/editor.js"></script>

<script type="text/javascript">
<!--

	function loadpage(root, path, file, name, language, type, value) {
		if (value) {
			document.editform.htmltext.value=value;
		} else if (window.opener && !window.opener.closed && window.opener.wgHTMLEditContent) {
			document.editform.htmltext.value=window.opener.wgHTMLEditContent.value;
		}
		document.editform.ContentLanguage.value=language;
		document.editform.htmltext.focus();
	}

	function checksubmit() {
		myEditor.saveHTML();
		if (window.opener && window.opener.wgHTMLEditContent) {
			window.opener.wgHTMLEditContent.value=document.editform.htmltext.value;
			window.close();
			return false;
		} else {
			return true;
		}
	}

// -->
</script>

<script type="text/javascript">
	// Initialization variables and calls;
	var root 	= "<?php echo addslashes($root); ?>";
	var path 	= "<?php echo addslashes($path); ?>";
	var file 	= "<?php echo addslashes($file); ?>";
	var name 	= "<?php echo addslashes($name); ?>";
	var language 	= "<?php echo addslashes($language); ?>";
	var type 	= "<?php echo addslashes($language); ?>";
	var value 	= "<?php echo addslashes($content); ?>";

	var myEditor;
	var callback = function(path) {}; // Callback function for browse windows.


	function attachEditor() {
		var Dom = YAHOO.util.Dom,
		Event = YAHOO.util.Event;
	
		var myConfig = {
//			height: "480px",
			width: "100%",
			animate: true,
			dompath: true,
			focusAtStart: true,
//			autoHeight: true,
			toolbar : {
				collapse: false,
				titlebar: 'Ariadne HTML Editor',
				draggable: false,
				buttonType: 'advanced',
				buttons: [
					{ group: 'actions', label: 'Actions',
						buttons: [
							{type: 'push', label: '<?php echo $ARnls['save']; ?>', value: 'save'}
//							{type: 'push', label: '<?php echo $ARnls['new']; ?>', value: 'new'},
//							{type: 'push', label: '<?php echo $ARnls['delete']; ?>', value: 'delete'}
						]
					},
				    { group: 'fontstyle', label: 'Font Name and Size',
				        buttons: [
				            { type: 'select', label: 'Arial', value: 'fontname', disabled: true,
				                menu: [
				                    { text: 'Arial', checked: true },
				                    { text: 'Arial Black' },
				                    { text: 'Comic Sans MS' },
				                    { text: 'Courier New' },
				                    { text: 'Lucida Console' },
				                    { text: 'Tahoma' },
				                    { text: 'Times New Roman' },
				                    { text: 'Trebuchet MS' },
				                    { text: 'Verdana' }
				                ]
				            },
				            { type: 'spin', label: '13', value: 'fontsize', range: [ 9, 75 ], disabled: true }
				        ]
				    },
				    { type: 'separator' },
				    { group: 'textstyle', label: 'Font Style',
				        buttons: [
				            { type: 'push', label: 'Bold CTRL + SHIFT + B', value: 'bold' },
				            { type: 'push', label: 'Italic CTRL + SHIFT + I', value: 'italic' },
				            { type: 'push', label: 'Underline CTRL + SHIFT + U', value: 'underline' },
				            { type: 'separator' },
				            { type: 'push', label: 'Subscript', value: 'subscript', disabled: true },
				            { type: 'push', label: 'Superscript', value: 'superscript', disabled: true },
				            { type: 'separator' },
				            { type: 'color', label: 'Font Color', value: 'forecolor', disabled: true },
				            { type: 'color', label: 'Background Color', value: 'backcolor', disabled: true },
				            { type: 'separator' },
				            { type: 'push', label: 'Remove Formatting', value: 'removeformat', disabled: true },
				            { type: 'push', label: 'Show/Hide Hidden Elements', value: 'hiddenelements' }
				        ]
				    },
				    { type: 'separator' },
				    { group: 'alignment', label: 'Alignment',
				        buttons: [
				            { type: 'push', label: 'Align Left CTRL + SHIFT + [', value: 'justifyleft' },
				            { type: 'push', label: 'Align Center CTRL + SHIFT + |', value: 'justifycenter' },
				            { type: 'push', label: 'Align Right CTRL + SHIFT + ]', value: 'justifyright' },
				            { type: 'push', label: 'Justify', value: 'justifyfull' }
				        ]
				    },
				    { type: 'separator' },
				    { group: 'parastyle', label: 'Paragraph Style',
				        buttons: [
				        { type: 'select', label: 'Normal', value: 'heading', disabled: true,
				            menu: [
				                { text: 'Normal', value: 'none', checked: true },
				                { text: 'Header 1', value: 'h1' },
				                { text: 'Header 2', value: 'h2' },
				                { text: 'Header 3', value: 'h3' },
				                { text: 'Header 4', value: 'h4' },
				                { text: 'Header 5', value: 'h5' },
				                { text: 'Header 6', value: 'h6' }
				            ]
				        }
				        ]
				    },
				    { type: 'separator' },
				    { group: 'indentlist', label: 'Indent/Lists',
				        buttons: [
				            { type: 'push', label: 'Indent', value: 'indent', disabled: true },
				            { type: 'push', label: 'Outdent', value: 'outdent', disabled: true },
				            { type: 'push', label: 'Create an Unordered List', value: 'insertunorderedlist' },
				            { type: 'push', label: 'Create an Ordered List', value: 'insertorderedlist' }
				        ]
				    },
				    { type: 'separator' },
				    { group: 'insertitem', label: 'Insert Item',
				        buttons: [
				            { type: 'push', label: 'HTML Link CTRL + SHIFT + L', value: 'createlink', disabled: true },
				            { type: 'push', label: 'Insert Image', value: 'insertimage' },
				            { type: 'push', label: 'Edit HTML Source', value: 'editcode' }
				        ]
				    }
				]
			}
		};

		// YAHOO.log('Create the Editor..', 'info', 'example');
		myEditor = new YAHOO.widget.Editor('htmltext', myConfig);
		// remove form deletion
		myEditor.invalidHTML = { };

		var state = 'off';
		myEditor.on('toolbarLoaded', function() {
			this.toolbar.on('saveClick', function() {
				myEditor.saveHTML();
				if (window.opener && window.opener.wgHTMLEditContent) {
					window.opener.wgHTMLEditContent.value=document.editform.htmltext.value;
					window.close();
				} else {
					document.getElementById("editform").submit();
				}
			}, this, true);
			
			this.toolbar.on('editcodeClick', function() {
				var ta = this.get('element'),
					iframe = this.get('iframe').get('element');

				if (state == 'on') {
					state = 'off';
					this.toolbar.set('disabled', false);
					YAHOO.log('Show the Editor', 'info', 'example');
					YAHOO.log('Inject the HTML from the textarea into the editor', 'info', 'example');
					this.setEditorHTML(ta.value);
					if (!this.browser.ie) {
						this._setDesignMode('on');
					}

					Dom.removeClass(iframe, 'editor-hidden');
					Dom.addClass(ta, 'editor-hidden');
					this.show();
					this._focusWindow();
				} else {
					state = 'on';
					YAHOO.log('Show the Code Editor', 'info', 'example');
					this.cleanHTML();
					YAHOO.log('Save the Editors HTML', 'info', 'example');
					Dom.addClass(iframe, 'editor-hidden');
					Dom.removeClass(ta, 'editor-hidden');
					this.toolbar.set('disabled', true);
					this.toolbar.getButtonByValue('editcode').set('disabled', false);
					this.toolbar.selectButton('editcode');
					this.dompath.innerHTML = 'Editing HTML Code';
					this.hide();
				}
				return false;
			}, this, true);

			this.on('cleanHTML', function(ev) {
				this.get('element').value = ev.html;
			}, this, true);
			
			this.on('afterRender', function() {
				var wrapper = this.get('editor_wrapper');
				wrapper.appendChild(this.get('element'));
				this.setStyle('width', '100%');
				this.setStyle('height', '100%');
				this.setStyle('visibility', '');
				this.setStyle('top', '');
				this.setStyle('left', '');
				this.setStyle('position', '');

				this.addClass('editor-hidden');
			}, this, true);
		}, myEditor, true);

		myEditor.render();
	}

	function overrideHyperlinkButton() {
		myEditor.on('toolbarLoaded', function() {
			//When the toolbar is loaded, add a listener to the insertimage button
			this.toolbar.on('createlinkClick', function() {
				//Get the selected element
				var _sel = this._getSelectedElement();
				var arpath; var artype; var arbehaviour; var aranchor; var name; var href;


				if (_sel.tagName == "A" && _sel.getAttribute) {
					arpath 		= _sel.getAttribute("ar:path");
					artype 		= _sel.getAttribute("ar:type");
					arbehaviour 	= _sel.getAttribute("ar:behaviour");
					arlanguage 	= _sel.getAttribute("ar:language");
					aranchor 	= _sel.getAttribute("ar:anchor");
					name		= _sel.getAttribute("name");
					url		= _sel.getAttribute("href");
					rel		= _sel.getAttribute("rel");
				}

				callback = function(settings) {
					var attributes = settings['attributes'];
					if (settings['href'] == '' && settings['name'] == '') {
						myEditor.execCommand('unlink');
					} else if (!attributes['ar:type']) {
						myEditor.execCommand('unlink');
					} else {
						myEditor.execCommand('createlink');
						var linkelm = myEditor.currentElement[0];
						for (i in attributes) {
							linkelm.setAttribute(i, attributes[i]);
						}
						if (settings['href']) {
							linkelm.setAttribute( 'href', settings['href'] );
						}
						if (settings['name']) {
							linkelm.setAttribute( 'name', settings['name'] );
						}
					}
				}

				// FIXME: If this is an exisiting hyperlink, pass ar:path and ar:type to the dialog somehow.
				var dialogUrl = 'dialog.hyperlink.php';
				if (artype) {
					dialogUrl += '?artype=' + escape(artype);
					if (arpath) {
						dialogUrl += '&arpath=' + escape(arpath);
					}
					if (arbehaviour) {
						dialogUrl += '&arbehaviour=' + escape(arbehaviour);
					}
					if (aranchor) {
						dialogUrl += '&aranchor=' + escape(aranchor);
					}
					if (arlanguage) {
						dialogUrl += '&arlanguage=' + escape(arlanguage);
					}
					if (name) {
						dialogUrl += '&name=' + escape(name);
					}
					if (url) {
						dialogUrl += '&url=' + escape(url);
					}
					if (rel) {
						dialogUrl += '&rel=' + escape(rel);
					}
				}
				win = window.open(dialogUrl, 'OBJECT_BROWSER', 'left=20,top=20,width=750,height=480,toolbar=0,resizable=0,status=0');
				if (!win) {
					//Catch the popup blocker
					alert('Please disable your popup blocker!!');
				}
				//This is important.. Return false here to not fire the rest of the listeners
				return false;
			}, this, true);
		}, myEditor, true);
	}

	function overrideImageButton() {
		myEditor.on('toolbarLoaded', function() {
			//When the toolbar is loaded, add a listener to the insertimage button
			this.toolbar.on('insertimageClick', function() {
				//Get the selected element
				var _sel = this._getSelectedElement();
				var arpath; var style; var alttext;

				//If the selected element is an image, do the normal thing so they can manipulate the image

				if (_sel.getAttribute) {
					arpath 		= _sel.getAttribute("ar:path");
					style	 	= _sel.getAttribute("ar:style");
					align	 	= _sel.getAttribute("align");
					alttext 	= _sel.getAttribute("alt");
				}

				callback = function(attributes) {
					myEditor.execCommand('insertimage');
					// FIXME: add ar:type and ar:path somehow;
					var imageelm = myEditor.currentElement[0];
					for (i in attributes) {
						imageelm.setAttribute(i, attributes[i]);
					}
				}

				var dialogUrl = 'dialog.image.php';
				if (arpath) {
					dialogUrl += '?arpath=' + escape(arpath);
					if (style) {
						dialogUrl += '&style=' + escape(style);
					}
					if (alttext) {
						dialogUrl += '&alttext=' + escape(alttext);
					}
					if (align) {
						dialogUrl += '&align=' + escape(align);
					}
				}

				win = window.open(dialogUrl, 'OBJECT_BROWSER', 'left=20,top=20,width=750,height=480,toolbar=0,resizable=0,status=0');

				if (!win) {
					//Catch the popup blocker
					alert('Please disable your popup blocker!!');
				}
				//This is important.. Return false here to not fire the rest of the listeners
				return false;
			}, this, true);
		}, myEditor, true);
	}

	function resizeEditor() {
		var h = document.getElementById("container").clientHeight;
		var th = (myEditor.toolbar.get('element').clientHeight + 2); //It has a 1px border..
		var dh = (myEditor.dompath.clientHeight + 1); //It has a 1px top border..
		var gh = document.body.clientHeight - document.getElementById("container").clientHeight;

		var newH = (h - th - dh - gh);
		var newW = document.getElementById("container").clientWidth;
		if (newW > 0) {
			myEditor.set('width', document.getElementById("container").clientWidth + 'px');
		}
		if (newH > 0) {
			myEditor.set('height', newH + 'px');
		}
	}

	window.onload = function () {
		// FIXME: replace with addEvent.
		loadpage(root, path, file, name, language, type, value);
		attachEditor();
		overrideImageButton();
		overrideHyperlinkButton();
		setTimeout(resizeEditor, 500);
	}

	window.onresize = function() {
		resizeEditor();
	}
</script>
<style type="text/css">
	html,body {
		height: 100%;
		width: 100%;
	}

	body {
		padding: 0px;
		margin: 0px;
		background-color: #C1CAE2;
	}

	.yui-skin-sam .yui-toolbar-container .yui-toolbar-editcode span.yui-toolbar-icon {
		background-image: url( "<?php echo $AR->dir->www; ?>js/yui/editor/assets/html_editor.gif" );
		background-position: 0 1px;
		left: 5px;
	}
	.yui-skin-sam .yui-toolbar-container .yui-button-editcode-selected span.yui-toolbar-icon {
		background-image: url( "<?php echo $AR->dir->www; ?>js/yui/editor/assets/html_editor.gif" );
		background-position: 0 1px;
		left: 5px;
	}
	.yui-skin-sam .yui-toolbar-container .yui-toolbar-save span.yui-toolbar-icon {
		background-image: url( "<?php echo $AR->dir->www; ?>images/icons/small/save.png");
		background-position: 0px 0px;
		left: 7px;
		top: 4px;
	}
	.yui-skin-sam .yui-toolbar-container .yui-button-save-selected span.yui-toolbar-icon {
		background-image: url( "<?php echo $AR->dir->www; ?>images/icons/small/save.png");
		background-position: 0px 0px;
		left: 7px;
		top: 4px;
	}
	.editor-hidden {
		visibility: hidden;
		top: -9999px;
		left: -9999px;
		position: absolute;
	}
	textarea {
		width: 100%;
		//height: 400px;
		border: 0;
		margin: 0;
		padding: 0;
		border: 1px solid #808080;
	}
	#container {
		position: absolute;
		left: 10px;
		top: 10px;
		bottom: 10px;
		right: 10px;
	}

	#buttons {
		position: relative;
		bottom: 0px;
		height: 30px;
		padding-top: 10px;		
	}

	#buttons input {
		float: right;
		margin-right: 10px;
	}

</style>
</head>
<body class="yui-skin-sam">
	<form id="editform" name="editform" method="post" action="<?php echo $root.$path.$file."edit.".$name.".save.phtml"; ?>" onSubmit="return checksubmit();">
		<div id="container">
			<input name="ContentLanguage" type="hidden" value="<?php echo $language; ?>">
			<textarea name="htmltext" id="htmltext" cols="75" rows="24"></textarea>
		</div>
	</form>
</body>
</html>
<?php
		} // end of else for editor check
	}
?>
