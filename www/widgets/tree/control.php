<?php
	$arUserPath = $HTTP_GET_VARS["arUserPath"];
	$interface = $HTTP_GET_VARS["interface"];
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../../styles/control.css">
<link rel="stylesheet" type="text/css" href="../../styles/button.css">
</head>
<body><div class="border-top"><div id="spanner" style="border: 0px; margin: 0px; padding: 0px; height: 28px;">
<table border="0" cellspacing="0" width="100%" align="center">
<tr>
  <form action="<?php echo $arUserPath; ?>logoff.phtml" target="_top">
    <td align="right" valign="middle">
<?php 
if ($interface=="winxp") { 
?>   
<span class="tbutton_right" style="float: right" onMouseOver="this.className='tbutton_right_hover';" 
     onMouseOut="this.className='tbutton_right';"><input type="image" src="../../images/winxp/logoff.png" value="Log Off" style="width: 80px;">
</span>
<?php
}else{
?>
<input type="submit" value="Log Off" style="width: 80px;">
<?php
}
?>
    </td>
  </form><form action="<?php echo $arUserPath; ?>tree.init.phtml" target="treeload">
    <td align="left" valign="middle">
<?php 
if ($interface=="winxp") { 
?> 
<span class="tbutton_right" style="float: right" onMouseOver="this.className='tbutton_right_hover';" 
     onMouseOut="this.className='tbutton_right';">
<input type="image" src="../../images/winxp/refresh.png" value="Refresh" style="width: 80px;">
</span>
<?php
}else{
?>
<input type="submit" value="Refresh" style="width: 80px;">
<?php
}
?>
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