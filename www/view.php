<?php
  require("./ariadne.inc");
  require($ariadne."/configs/ariadne.phtml");

  $PATH_INFO = $HTTP_SERVER_VARS["PATH_INFO"];
?>
<html>
<head>
  <script>
    function LoadingDone() {
		parent.LoadingDone();
	}
	function Get(varname) {
		return parent.Get(varname);
	}
	function Set(varname, value) {
		return parent.Set(varname, value);
	}
  </script>
</head>
<frameset rows="30,*" BORDER="0" FRAMEBORDER="no" FRAMESPACING="0">
  <frame src="<?php echo $AR->root.$PATH_INFO; ?>classic.toolbar.phtml?template=<?php echo $template; ?>" name="toolbar" marginwidth="0" marginheight="0" SCROLLING="no">
  <frame src="<?php echo $AR->root.$PATH_INFO.$template; ?>" name="object" marginwidth="0" marginheight="0">
</frameset>
</html>
