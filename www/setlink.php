<html>
<body bgcolor="white">
<script>
<!--
  link='<?php echo $link; ?>';
  if (top.tree) {
    <?php 
      switch($do) {
        case "add" :
          echo "    top.tree.AddLinks(link, '$icon','".AddSlashes($name)."','$path','".AddSlashes($pre)."');\n";
          if ($shortcut) {
            echo "    top.tree.AddLinks('$shortcut','$icon','".AddSlashes($name)."','$path','".AddSlashes($pre)."');\n";
          }
          echo "    document.location.href='$returnpage';\n";
          break;

        case "update" : 
          echo "    top.tree.UpdateLinks('$icon','".AddSlashes($name)."', '$path','".AddSlashes($pre)."');\n";
          echo "    document.location.href='$returnpage';\n";
          break;

        case "delete" :
          echo "    top.tree.DelLinks('$path');\n";
          if (!$parent) {
            echo "    document.location.href='$returnpage;';\n";
          } else {
            echo "    top.View('$parent');\n";
          }
          break;
      }
    ?> 
  } else {
    document.location.href='<?php echo $returnpage; ?>';
  }
//-->
</script>
<noscript>
  <META HTTP-EQUIV="Refresh" content="0;URL=<?php echo $returnpage; ?>">
  <a href="<?php echo $returnpage; ?>">Continue</a>.
</noscript>
</body>
</html>