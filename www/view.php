<?php
  require("./ariadne.inc");
  require($ariadne."/configs/ariadne.phtml");
?>
<html>
<frameset rows="30,*" BORDER="0" FRAMEBORDER="no" FRAMESPACING="0">
  <frame src="<?php echo $AR->root.$PATH_INFO; ?>toolbar.phtml" name="toolbar" marginwidth="0" marginheight="0" SCROLLING="no">
  <frame src="<?php echo $AR->root.$PATH_INFO.$template; ?>" name="object" marginwidth="0" marginheight="0">
</frameset>
</html>
