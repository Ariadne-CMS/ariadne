<html>
<head>
<link rel="stylesheet" type="text/css" href="../../styles/control.css">
<style>
INPUT {
	FONT: 13px Arial, sans-serif;
}
</style>
</head>
<body><div class="border-top"><div id="spanner" style="border: 0px; margin: 0px; padding: 0px; height: 28px;">
<table border="0" cellspacing="0" width="100%" align="center">
<tr>
  <form action="<?php echo $arUserPath; ?>logoff.phtml" target="_top">
    <td align="right" valign="top">
      <input type="submit" value="Log Off" style="width: 75px;">&nbsp;
    </td>
  </form><form action="<?php echo $arUserPath; ?>tree.init.phtml" target="treeload">
    <td align="left" valign="top">
      &nbsp;<input type="submit" value="Refresh" style="width: 75px;">
    </td>
  </form>
</tr>
</table> 
</div></div>
<script>
  if (document.all) {
    document.body.style.border='1px inset';
    spanner.style.height='26px';
  }
</script>
</body>
</html>