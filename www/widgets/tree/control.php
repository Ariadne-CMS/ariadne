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
<table border="0" height="100%" cellspacing="0" width="100%" align="center">
<tr>
  <form action="<?php echo $arUserPath; ?>logoff.phtml" target="_top">
    <td align="right" valign="top">
      <input type="submit" value="Log Off">&nbsp;
    </td>
  </form><form action="<?php echo $arUserPath; ?>tree.init.phtml" target="treeload">
    <td align="left" valign="top">
      &nbsp;<input type="submit" value="Refresh">
    </td>
  </form>
</tr>
</table> 
</body>
</html>