<?php
  include($this->code."stores/modules/sql_compiler.php");

  class mssql_compiler extends sql_compiler {
	function mssql_compiler($tbl_prefix="", $limit=100, $offset=0) {
		$this->tbl_prefix	= $tbl_prefix;
		$this->limit		= $limit;
		$this->offset		= $offset;
	}

	function compile_tree(&$node) {
	debug("compile tree: $node[id]","all");
		switch ((string)$node["id"]) {
			case 'ident':
				$table=$this->tbl_prefix.$node["table"];
				$this->used_tables[$table]=$table;
				$field=$node["field"];
				$result=" $table"."."."$field ";
			break;
			case 'string':
			case 'float':
			case 'int':
				$result=" ".$node["value"]." ";
			break;
			case 'and':
				$left=$this->compile_tree($node["left"]);
				$right=$this->compile_tree($node["right"]);
				$result=" $left and $right ";
			break;
			case 'or':
				$left=$this->compile_tree($node["left"]);
				$right=$this->compile_tree($node["right"]);
				$result=" $left or $right ";
			break;
			case 'cmp':
				switch ($node["operator"]) {
					case '=':
					case '==':
						$operator="=";
					break;
					case '!=':
						$operator="<>";
					break;
					case '<=':
					case '>=':
					case '<':
					case '>':
						$operator=$node["operator"];
					break;
					case '~=':
						$operator="LIKE";
					break;
					case '!~':
						$operator="NOT LIKE";
					break;
				}
				if ($node["left"]["id"]!=="implements") {
					$left=$this->compile_tree($node["left"]);
					$right=$this->compile_tree($node["right"]);
					$result=" $left $operator $right ";
				} else {
					$table=$this->tbl_prefix."types";
					$this->used_tables[$table]=$table;
					$type=$this->compile_tree($node["right"]);
					$result=" (".$this->tbl_prefix."types.implements $operator $type and ".$this->tbl_prefix."objects.vtype = ".$this->tbl_prefix."types.type ) ";
				}
			break;
			case 'group':
				$left=$this->compile_tree($node["left"]);
				if ($left) {
					$result=" ( $left ) ";
				}
			break;

			case 'orderby':
				$result=$this->compile_tree($node["left"]);
				$this->orderby_s=$this->compile_tree($node["right"]);
			break;

			case 'orderbyfield':
				$left=$this->compile_tree($node["left"]);
				$right=$this->compile_tree($node["right"]);
				if ($left) {
					$result=" $left ,  $right ".$node["type"]." ";
				} else {
					$result=" $right ".$node["type"]." ";
				}
			break;

			case 'limit':
				$this->where_s=$this->compile_tree($node["left"]);
				if ($node["limit"]) {
					$this->limit_s=" top ".$node["limit"]." ";
				}
			break;
		}
		return $result;
	}

	// mssql specific compiler function
	function priv_sql_compile($tree) {
		$this->used_tables="";
		$this->compile_tree($tree);
		$nodes=$this->tbl_prefix."nodes";
		$objects=$this->tbl_prefix."objects";
		$this->used_tables[$nodes]=$nodes;
		$this->used_tables[$objects]=$objects;
		@reset($this->used_tables);
		while (list($key, $val)=each($this->used_tables)) {
			if ($tables) {
				$tables.=", $val";
			} else {
				$tables="$val";
			}
			if (substr($val, 0, 5+strlen($this->tbl_prefix)) == $this->tbl_prefix."prop_") {
				$prop_dep.=" and $val.object=$objects.id ";
			}
		}

		$query="select ";
		if (!$this->limit_s && $this->limit) {
			$this->limit_s="top ".$this->limit." ";//,".$this->offset;
		}
		$query.=" $this->limit_s ";
		$query.="($nodes.path), $nodes.parent, $nodes.priority, ";
		$query.=" $objects.id, $objects.type, $objects.object, $objects.lastchanged as lastchanged, $objects.vtype ";
		$query.=" from $tables where ";
		$query.=" $nodes.object=$objects.id $prop_dep";
		$query.=" and ( $this->where_s ) ";
		if ($this->orderby_s) {
			$query.= " order by $this->orderby_s, $nodes.priority DESC, $nodes.path ASC ";
		} else {
			$query.= " order by $nodes.priority DESC, $nodes.path ASC ";
		}

		return $query;
	}

  }

?>