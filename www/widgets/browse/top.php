<head>
<script language="javascript">
<!--
current=new String('<?php echo $PATH_INFO; ?>');

function SetPath(path) {

  current=new String(path);
  document.forms[0].path.value=path;

}

function GotoPath() {
  path=document.forms[0].path.value;
  top.GotoPath(path);
  return 1;
}

// -->
</script>
</head>
<body bgcolor="#EEEEEE">
<font face="helvetica,sans-serif" size="-1">
<form onSubmit='return GotoPath();'>
<nobr><input type="button" name="up" value="Up" onClick="top.listpaths.GoUp()"><input type="text" name="path" value="<?php echo $PATH_INFO; ?>"></nobr>
</form>
</body>