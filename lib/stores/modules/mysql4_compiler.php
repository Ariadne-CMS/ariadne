<?php
  include_once($this->code."stores/modules/mysql_compiler.php");

  class mysql4_compiler extends mysql_compiler {
	public function __construct(&$store, $tbl_prefix="") {
		debug("mysql4_compiler($tbl_prefix)", "store");
		$this->tbl_prefix=$tbl_prefix;
		$this->store=$store;
	}

	protected function compile_tree(&$node) {
		switch ((string)$node["id"]) {
			case 'cmp':
				if ($node["left"]["id"] == "implements" && $node["operator"] == "!=") {
					$table=$this->tbl_prefix."types";
					$type=$this->compile_tree($node["right"]);
					$operator=$node["operator"];

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
					$result = parent::compile_tree($node);
				}
			break;
			default:
				$result = parent::compile_tree($node);
			break;
		}
		return $result;
	}

	protected function priv_sql_compile($tree) {
		$result = parent::priv_sql_compile($tree);
		return $result;
	}
  }

?>