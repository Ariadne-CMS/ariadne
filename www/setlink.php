<html>
<body bgcolor="white">
  <script>
  <!--
    link='<?php echo $link; ?>';
    if (top.tree) {
      <?php switch($do) {
        case "add" : ?>
        top.tree.AddLinks(link, '<?php echo $icon ?>','<?php echo AddSlashes($name); ?>','<?php echo $path; ?>','<?php echo AddSlashes($pre); ?>');
        document.location.href='<?php echo $returnpage; ?>';
      <?php break;
        case "update" : ?>
        top.tree.UpdateLinks('<?php echo $icon; ?>','<?php echo AddSlashes($name); ?>', '<?php echo $path; ?>','<?php echo AddSlashes($pre); ?>');
        document.location.href='<?php echo $returnpage; ?>';
      <?php break;
        case "delete" : ?>
        top.tree.DelLinks('<?php echo $path; ?>');
        <?php if (!$parent) { ?>
          document.location.href='<?php echo $returnpage; ?>';
        <?php } else { ?>
          top.View('<?php echo $parent; ?>');
        <?php } ?>
      <?php } ?>
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