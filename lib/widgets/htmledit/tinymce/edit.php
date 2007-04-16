<html>
<head>
<title><?php echo $ARnls["edit"]." ".$path; ?></title>
<script>
<!--
	var field;

	var imageInfoArr;

<?php
        // load editor.ini, in case the editor is started directly, not through the
        // js.html file
        $options=$this->call("editor.ini");

        function make_ini_options($name, $option) {
                if (is_array($option)) {
                        reset($option);
                        echo "  $name = new Array();\n";
                        while (list($key, $value)=each($option)) {
                                make_ini_options($name."[\"$key\"]", $value);
                        }
                } else
                if (is_string($option)) {
                        echo "  $name = \"".AddCSlashes($option, ARESCAPE)."\";\n";
                } else {
                        echo "  $name = ".(int)$option.";\n";
                }
        }

        echo "var ";
        make_ini_options("tbContentEditOptions", $options);

?>
  if (!tbContentEditOptions['editor.ini']) {
    tbContentEditOptions['editor.ini']='<?php echo $this->path; ?>';
  }





	function loadpage(root, path, file, name, language, type, value) {
		if (value) {
			document.editform.htmltext.value=value;
		} else if (window.opener && !window.opener.closed && window.opener.wgHTMLEditContent) {
			document.editform.htmltext.value=window.opener.wgHTMLEditContent.value;
		}
		document.editform.ContentLanguage.value=language;
		document.editform.htmltext.focus();
	
		tinyMCE.updateContent("htmltext");
		tinyMCE.setContent(window.opener.wgHTMLEditContent.value);
	}

	function checksubmit() {
		tinyMCE.triggerSave();
		if (window.opener && window.opener.wgHTMLEditContent) {
			window.opener.wgHTMLEditContent.value=document.editform.htmltext.value;
			window.close();
			return false;
		} else {			
			return true;
		}
	}

	function getImageInfoArr() {
		return imageInfoArr;
	}

	function getImageInfo() {
		var imageurl = "<?php echo $this->make_url("/") . $this->currentsite() . "edit.object.html.image.tinymce.phtml";?>";

		var result = Array();
		result['imageurl'] = imageurl;
		result['editOptions'] = tbContentEditOptions;

		return result;
	}

	function setImageInfo(imageArr) {
		imageInfoArr = imageArr;
		imageInfoArr['editOptions'] = tbContentEditOptions;
	}

	function ariadneExecCommandHandler(editor_id, elm, command, user_interface, value) {
                switch (command) {
			case "mceImage":
				var src = "", alt = "", border = "", hspace = "", vspace = "", width = "", height = "", align = "";
				var title = "", onmouseover = "", onmouseout = "", action = "insert", arpath="", artype="";
				var img = tinyMCE.imgElement;
				var inst = tinyMCE.getInstanceById(editor_id);

				if (tinyMCE.selectedElement != null && tinyMCE.selectedElement.nodeName.toLowerCase() == "img") {
					img = tinyMCE.selectedElement;
					tinyMCE.imgElement = img;
				}

				if (img) {
					// Is it a internal MCE visual aid image, then skip this one.
					if (tinyMCE.getAttrib(img, 'name').indexOf('mce_') == 0)
						return true;

					src = tinyMCE.getAttrib(img, 'src');
					alt = tinyMCE.getAttrib(img, 'alt');

					// Try polling out the title
					if (alt == "")
						alt = tinyMCE.getAttrib(img, 'title');

					// Fix width/height attributes if the styles is specified
					if (tinyMCE.isGecko) {
						var w = img.style.width;
						if (w != null && w != "")
							img.setAttribute("width", w);

						var h = img.style.height;
						if (h != null && h != "")
							img.setAttribute("height", h);
					}

					border = tinyMCE.getAttrib(img, 'border');
					hspace = tinyMCE.getAttrib(img, 'hspace');
					vspace = tinyMCE.getAttrib(img, 'vspace');
					width = tinyMCE.getAttrib(img, 'width');
					height = tinyMCE.getAttrib(img, 'height');
					align = tinyMCE.getAttrib(img, 'align');
					onmouseover = tinyMCE.getAttrib(img, 'onmouseover');
					onmouseout = tinyMCE.getAttrib(img, 'onmouseout');
					arpath = tinyMCE.getAttrib(img, 'ar:path');
					artype = tinyMCE.getAttrib(img, 'ar:type');

					title = tinyMCE.getAttrib(img, 'title');

					// Is realy specified?
					if (tinyMCE.isMSIE) {
						width = img.attributes['width'].specified ? width : "";
						height = img.attributes['height'].specified ? height : "";
					}

					//onmouseover = tinyMCE.getImageSrc(tinyMCE.cleanupEventStr(onmouseover));
					//onmouseout = tinyMCE.getImageSrc(tinyMCE.cleanupEventStr(onmouseout));

					src = eval(tinyMCE.settings['urlconverter_callback'] + "(src, img, true);");

					// Use mce_src if defined
					mceRealSrc = tinyMCE.getAttrib(img, 'mce_src');
					if (mceRealSrc != "") {
						src = mceRealSrc;

						if (tinyMCE.getParam('convert_urls'))
							src = eval(tinyMCE.settings['urlconverter_callback'] + "(src, img, true);");
					}

					//if (onmouseover != "")
					//	onmouseover = eval(tinyMCE.settings['urlconverter_callback'] + "(onmouseover, img, true);");

					//if (onmouseout != "")
					//	onmouseout = eval(tinyMCE.settings['urlconverter_callback'] + "(onmouseout, img, true);");

					action = "update";
				}

				var template = new Array();

				//template['file'] = 'image.htm?src={$src}';
				template['file'] = '<?php echo $this->make_url("/") . $this->currentsite() . "edit.object.html.image.tinymce.phtml";?>';
				template['width'] = 640;
				template['height'] = 480 + (tinyMCE.isMSIE ? 25 : 0);

				// Language specific width and height addons
				template['width'] += tinyMCE.getLang('lang_insert_image_delta_width', 0);
				template['height'] += tinyMCE.getLang('lang_insert_image_delta_height', 0);

				if (inst.settings['insertimage_callback']) {
					var returnVal = eval(inst.settings['insertimage_callback'] + "(src, alt, border, hspace, vspace, width, height, align, title, onmouseover, onmouseout, action);");
					if (returnVal && returnVal['src'])
						TinyMCE_AdvancedTheme._insertImage(returnVal['src'], returnVal['alt'], returnVal['border'], returnVal['hspace'], returnVal['vspace'], returnVal['width'], returnVal['height'], returnVal['align'], returnVal['title'], returnVal['onmouseover'], returnVal['onmouseout']);
				} else {
					tinyMCE.openWindow(template, {src : src, alt : alt, border : border, hspace : hspace, vspace : vspace, width : width, height : height, align : align, title : title, onmouseover : onmouseover, onmouseout : onmouseout, action : action, inline : "yes", arpath : arpath, artype : artype, 'editOptions' : tbContentEditOptions});
				}
				return true;
		}
		return false;
	}


// -->
</script>
<?php
    include($this->store->get_config("code")."widgets/wizard/classic.css");
?>
<style>
  TEXTAREA {
    width: 100%;
    height: 94%;
  }
</style>
</head>
<body bgcolor="#BFBFBF" onLoad="loadpage('<?php echo $root; ?>','<?php echo $path; ?>','<?php echo $file; ?>','<?php echo $name; ?>','<?php echo $language; ?>','<?php echo $language; ?>','<?php echo $content; ?>')">

<!-- tinyMCE -->
<script language="javascript" type="text/javascript" src="<?php echo $AR->dir->www; ?>widgets/htmledit/tinymce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
   tinyMCE.init({
         theme : "advanced",
         plugins : "table,preview,searchreplace",
         theme_advanced_buttons2_add : "preview,separator,search,replace",
         theme_advanced_buttons3_add_before : "tablecontrols,separator",
         theme_advanced_toolbar_location : "top",
         theme_advanced_toolbar_align : "left",
	 execcommand_callback : "ariadneExecCommandHandler",
         mode : "exact",
         elements : "htmltext",
         extended_valid_elements : "a[href|target|name]",
         language : "<?php echo $language; ?>",
         file_browser_callback : "fileBrowserCallBack",
         auto_cleanup_word : "true",
	 cleanup : "false"
	
   });
</script>
<!-- /tinyMCE -->


<form name="editform" method="post" action="<?php echo $root.$path.$file."edit.".$name.".save.phtml"; ?>" onSubmit="return checksubmit();">
<input name="ContentLanguage" type="hidden" value="<?php echo $language; ?>">
<table width="100%" height="100%" border="0" cellspacing="0">
  <tr style="height: 100%">
    <td>
      <div style="width: 100%; height: 100%">
        <textarea style="width: 100%; height: 100%" name="htmltext" cols="75" rows="24" wrap="soft"></textarea>
      </div>
    </td>
  </tr><tr style="height: 50px;">
	<td align="right">
		<input type="button" name="cancel" value="<?php echo $ARnls["cancel"]; ?>" onClick="window.close()">
		<input type="submit" name="save" value="<?php echo $ARnls["save"]; ?>">
	</td>
  </tr>
</table>
</form>
</body>
</html>

