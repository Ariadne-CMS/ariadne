<html>
<head>
<script language="javascript">
	function sbar(text) {
		if (document.layers) {
			bar=document.layers.statusbar;
			bar.document.open();
			bar.document.write(text);
			bar.document.close();
			bar.visibility="show";
		} else {
			bar=document.all["statusbarIE"];
			bar.innerHTML=text;
		}
	}

</script>
</head>
<br><br><br>
<body bgcolor="#FFFFFF">
<table border="0" width="100%">
<tr valign="top">
	<td><font face="helvetica,sans-serif">
	<?php echo $message; ?>
	</font></td>
</tr>
</table>

<layer id="statusbar" style="visibility: show"></layer>
<div id="statusbarIE">&nbsp;</div>
<table ><tr><td><br></td></tr></table>

<table border="0" width="100%" bgcolor="#404074">
<tr valign="middle">
	<td align="right">
	<form>
		<input type="button" value="cancel" onclick="top.close();">
	</form>
	</td>
</tr>
</table>

</body>
</html>
