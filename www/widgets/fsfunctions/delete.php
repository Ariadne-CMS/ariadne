<html>
<head>
</head>
<frameset rows="1,*" BORDER="0" FRAMEBORDER="no" FRAMESPACING="0">
	<frame name="action" SCROLLING="no" FRAMEBORDER="no" MARGINHEIGHT="0" MARGINWIDTH="0" src="<?php echo $root.$path."delete.js.phtml"; ?>"> 
	<frame name="dialog" SCROLLING="auto" FRAMEBORDER="no" MARGINHEIGHT="0" MARGINWIDTH="0" src="dialog.php?message=<?php echo urlencode($message); ?>">
</frameset>
</html>
