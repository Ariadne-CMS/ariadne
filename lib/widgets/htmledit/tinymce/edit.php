<html>
<head>
<title><?php echo $ARnls["edit"]." ".$path; ?></title>
<script>
<!--

	function loadpage(root, path, file, name, language, type, value) {
		if (value) {
			document.editform.htmltext.value=value;
		} else if (window.opener && !window.opener.closed && window.opener.wgHTMLEditContent) {
			document.editform.htmltext.value=window.opener.wgHTMLEditContent.value;
		}
		document.editform.ContentLanguage.value=language;
		document.editform.htmltext.focus();
		tinyMCE.updateContent("htmltext");
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
         mode : "exact",
         elements : "htmltext",
         extended_valid_elements : "a[href|target|name]",
         language : "<?php echo $language; ?>",
         auto_cleanup_word : "true"

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

