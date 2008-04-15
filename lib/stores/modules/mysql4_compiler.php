<?php
  include_once($this->code."stores/modules/mysql_compiler.php");

  class mysql4_compiler extends sql_compiler {
	function mysql4_compiler(&$store, $tbl_prefix="") {
		debug("mysql4_compiler($tbl_prefix)", "store");
		$this->tbl_prefix=$tbl_prefix;
		$this->store=$store;
	}

	function compile_tree(&$node) {
		switch ((string)$node["id"]) {
			case 'cmp':
				if ($node["left"]["id"] == "implements" && $node["operator"] == "!=") {
					$table=$this->tbl_prefix."types";
					$type=$this->compile_tree($node["right"]);
					switch ($operator) {
						case '!=':
							$result=" (".$this->tbl_prefix."objects.type not in (select type from ".$this->tbl_prefix."types where implements = $type )) ";
						break;
						default:
							$this->used_tables[$table]=$table;
							$result=" (".$this->tbl_prefix."types.implements $operator $type and ".$this->tbl_prefix."objects.vtype = ".$this->tbl_prefix."types.type ) ";
						break;
					}
				} else {
					$result = mysql_compiler::compile_tree($node);
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
