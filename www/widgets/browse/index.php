<html>
<script>
<!-- 
  function GotoPath(path) {
    listpaths.location.href='<?php echo $loader; ?>'+path+'browselist.phtml';
  }

  function ReturnPath(path) {
    window.opener.wgBrowseResult.value=path;
    if (window.opener.wgBrowseAction) {
      window.opener.wgBrowseAction('<?php echo AddCSlashes($name, "'\"\\\n\r"); ?>', path);
    }
    top.window.close();
  }

  self.document.writeln('<frameset rows="40,*,40" frameborder="0" border="0" marginheight="0" marginwidth="0" scrolling="no">'); 
  self.document.writeln('  <frame src="top.php<?php echo $path; ?>" name="path" border="0" marginheight="0" marginwidth="0" scrolling="no">'); 
  self.document.writeln('  <frame src="<?php echo $loader.$path; ?>browselist.phtml" name="listpaths" border="0" marginheight="0" marginwidth="0" scrolling="auto">'); 
  self.document.writeln('  <frame src="bottom.php<?php echo $path; ?>" name="commit" border="0" marginheight="0" marginwidth="0" scrolling="no">'); 
  self.document.writeln('</frameset>');
// -->
</script>
<noscript>
<body bgcolor="white">
Your browser cannot run JavaScript code, please upgrade to a newer browser.
</body>
</noscript>
</html>