<?php
  global $HTTP_USER_AGENT;
  if (eregi(".*msie ([0-9]+).*",$HTTP_USER_AGENT, $regs) &&
       $regs[1] && (intval($regs[1])>=4)) { //ie4+
    include("./ie/edit.php");
  } else {
    if ($file) {
      $file.="/";
    }
?>
<html>
<head>
<script>
<!--
  function init() {
    document.editform.htmltext.value=window.opener.GetHTML('<?php echo $name; ?>');
  }
// -->
</script>
</head>
<body bgcolor="white" onLoad="init()">
<form name="editform" method="post" action="<?php echo $root.$path.$file."edit.".$name.".save.phtml"; ?>">
<input name="ContentLanguage" type="hidden" value="<?php echo $language; ?>">
<table width="100%" border="0" cellspacing="0"><tr>
  <td colspan="2">
    <textarea name="htmltext" cols="75" rows="24" wrap="soft"><?php
      if ($file) {
        $file.="/";
      }
      // FIXME: this won't work: no user/password given
      $fp=fopen($root.$path.$file."show.".$name.".phtml","r");
      while ($buffer=fread($fp, 10000)) {
        echo $buffer;
      }
      fclose($fp);
    ?></textarea>
  </td>
</tr><tr bgcolor="#404074">
  <td>&nbsp;</td>
  <td align="right">
    <input type="button" name="cancel" value="Cancel" onClick="window.close()">
    <input type="submit" name="save" value="Save">
  </td>
</tr></table>
</form>
</body>
</html>
<?php
  }
?>