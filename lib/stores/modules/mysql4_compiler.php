<?php
  include_once($this->code."stores/modules/mysql_compiler.php");

  class mysql4_compiler extends sql_compiler {
	function mysql4_compiler($tbl_prefix="") {
		debug("mysql4_compiler($tbl_prefix)", "store");
		$this->tbl_prefix=$tbl_prefix;
	}


	function priv_sql_compile($tree) {
		$result = mysql_compiler::priv_sql_compile($tree);
		return $result;
	}
  }

?>