<?php
  include_once($this->code."stores/modules/mysql_compiler.php");
  // BACKWARDS compatibility wrapper
  // the default mysql store also implement this logic
  class mysql4_compiler extends mysql_compiler { }

?>