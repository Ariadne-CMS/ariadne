<?php
  require("../../ariadne.inc");
  require($ariadne."/configs/ariadne.phtml");
  if (!$title) {
    $title="Upload File";
  }
  if (!$name) {
    $name="file";
  }
?><html>
<head>
<title><?php echo $title; ?></title>
</head>
<body bgcolor="white">
&nbsp;<br>
&nbsp;<br>
<center>
<form align="center" action="<?php echo $root.$path."newfile.phtml"; ?>" ENCTYPE="multipart/form-data" method="post" >
<font face="helvetica, sans-serig">File&nbsp;:
<input type="hidden" name="MAX_FILE_SIZE" value="10000000"> 
<input type="hidden" name="returnpage" value="<?php 
  echo $AR->dir->www; ?>widgets/upload/upload.php?path=<?php 
  echo RawUrlEncode($path); ?>&root=<?php echo RawUrlEncode($root); ?>">
<input type="file" name="file">
<input type="submit" value="Send">
</font>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
<input type="button" value="Cancel" onClick="top.window.close()">
</form>

</body>
</html>