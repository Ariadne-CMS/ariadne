<html>
<head>
<link rel="stylesheet" type="text/css" href="../../styles/control.css">
<style>
INPUT {
	FONT: 13px Arial, sans-serif;
}
</style>
</head>
<body>
<table border="0" height="100%" cellpadding="0" cellspacing="0" width="90%" align="center">
<tr>
  <form action="<?php echo $arUserPath; ?>logoff.phtml" target="_top">
    <td align="left" valign="top">
      <input type="submit" value="Log Off">
    </td>
  </form><form action="<?php echo $arUserPath; ?>tree.init.phtml" target="treeload">
    <td align="right" valign="top">
      <input type="submit" value="Refresh">
    </td>
  </form>
</tr>
</table>
</body>
</html>