<html>
<head>
<style>
TD {
	FONT: 13px sans-serif;
}
BODY 	{
	FONT: 13px sans-serif;
	BORDER: 0;
  <?php
    global $HTTP_USER_AGENT;
    if (eregi(".*msie ([0-9]+).*",$HTTP_USER_AGENT, $regs) &&
       $regs[1] && (intval($regs[1])>=4)) { //ie4+
  ?>
	BACKGROUND-COLOR: buttonface;
  <?php
    } else {
  ?>
	BACKGROUND-COLOR: #BBBBBB;
  <?php
    }
  ?>
}
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