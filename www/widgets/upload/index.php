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
<script>
<!-- 
  self.document.writeln('<frameset rows="30,*" frameborder="0" border="0" marginheight="0" marginwidth="0" scrolling="no">'); 
  self.document.writeln('  <frame src="top.php?title=<?php echo RawUrlEncode($title); ?>&path=<?php echo RawUrlEncode($path); ?>" name="commit" border="0" marginheight="0" marginwidth="0" scrolling="no">'); 
  self.document.writeln('  <frame src="upload.php?path=<?php echo RawUrlEncode($path); ?>&root=<?php echo RawUrlEncode($root); ?>" name="upload" border="0" marginheight="0" marginwidth="0" scrolling="auto">'); 
  self.document.writeln('</frameset>');
// -->
</script>
<noscript>
<body bgcolor="white">
Your browser cannot run JavaScript code, please upgrade to a newer browser.
</body>
</noscript>
</html>