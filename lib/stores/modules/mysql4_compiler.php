<?php
  include_once($this->code."stores/modules/mysql_compiler.php");

  class mysql4_compiler extends sql_compiler {
	function mysql4_compiler($tbl_prefix="") {
		debug("mysql4_compiler($tbl_prefix)", "store");
		$this->tbl_prefix=$tbl_prefix;
	}

	function compile_tree(&$node) {
		switch ((string)$node["id"]) {
			case 'cmp':
				switch ($node["operator"]) {
					case '=*':
					case '!*':
					case '=**':
					case '!**':
						if ($node["left"]["id"]!=="implements") {
							$left=$this->compile_tree($node["left"]);
							$right=$this->compile_tree($node["right"]);
							/* fulltext search operators: =*, !*, =**, !** (double asterices indicate boolean mode) */
							if (substr($node["operator"], 1, 1)=='*') {
								$operator = $node["operator"];
								$not = substr($operator, 0, 1)=='!' ? " not" : "";
								$boolmode = substr($operator, 2)=='*' ? " in boolean mode" : "";
								$result = "$not match ($left) against ('".mysql4store::format_for_fti(substr($right,1,-1))."$boolmode') ";
							} else {
								/* lastchanged == unixtimestamp -> lastchanged == 200201.. */
								if ($node["left"]["field"]=="lastchanged") {
									$right = date("YmdHis", $right);
								}
								$result=" $left $operator $right ";
							}
						} else {
							$result = mysql_compiler::compile_tree($node);
						}
					break;
					default:
						$result = mysql_compiler::compile_tree($node);
					break;
				}
			break;
			default:
				$result = mysql_compiler::compile_tree($node);
			break;
		}
		return $result;
	}


	function priv_sql_compile($tree) {
		$result = mysql_compiler::priv_sql_compile($tree);
		return $result;
	}
  }

?>