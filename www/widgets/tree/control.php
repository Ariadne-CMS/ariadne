<html>
<head>
<style>
TD {
	FONT: 13px sans-serif;
}
BODY 	{
	FONT: 13px sans-serif;
	BORDER: 0;
	BACKGROUND-COLOR: #BBBBBB;
}
@import('../../background.css');
INPUT {
	FONT: 13px sans-serif;
}
</style>
</head>
<body>
<fieldset>
<table border="0" height="100%" cellpadding="1" cellspacing="0" width="90%" align="center">
<tr>
  <form action="<?php echo $PATH_INFO; ?>logoff.phtml" target="_top">
    <td align="left" valign="middle">
      <input type="submit" value="Log Off">
    </td>
  </form><form action="<?php echo $PATH_INFO; ?>treeinit.phtml" target="treeload">
    <td align="right" valign="middle">
      <input type="submit" value="Refresh">
    </td>
  </form>
</tr>
</table>
</fieldset>
</body>
</html>