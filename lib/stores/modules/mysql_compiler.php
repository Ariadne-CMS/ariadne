<?php
  include_once($this->code."stores/modules/sql_compiler.php");

  class mysql_compiler extends sql_compiler {
	function mysql_compiler(&$store, $tbl_prefix="") {
		debug("mysql_compiler($tbl_prefix)", "store");
		$this->tbl_prefix=$tbl_prefix;
		$this->store=$store;
	}

	function compile_tree(&$node) {
		switch ((string)$node["id"]) {
			case 'property':
				$table=$this->tbl_prefix.$node["table"];
				$field=$node["field"];
				$record_id=$node["record_id"];
				if (!$record_id) {
					$this->used_tables[$table]=$table;
					if (!$this->in_orderby) {
						$result=" $table.object = ".$this->tbl_prefix."objects.id and $table.$field ";
					} else {
						/*
							if we are parsing 'orderby' properties we have to
							join our tables for the whole query
						*/							
						$this->select_tables[$table]=$table;
						$result=" $table.$field ";
					}
				} else {
					$this->used_tables["$table as $table$record_id"] = $table.$record_id;
					if (!$this->in_orderby) {
						$result=" $table$record_id.object = ".$this->tbl_prefix."objects.id and $table$record_id.$field ";
					} else {
						$this->select_tables["$table as $table$record_id"] = $table.$record_id;
						$result=" $table$record_id.$field ";
					}
				}
			break;
			case 'ident':
				$table=$this->tbl_prefix.$node["table"];
				$field=$node["field"];
				$this->used_tables[$table]=$table;
				$result=" $table.$field ";
			break;
			case 'custom':
				$table = $this->tbl_prefix."prop_custom";
				$field = $node["field"];
				$nls = $node["nls"];
				/*
					when we are compiling orderby properties we always want
					to assign it to a new table alias
				*/
				if ($this->in_orderby) {
					$this->custom_id++;
				}
				$this->custom_ref++;
				$this->used_tables[$table." as $table".$this->custom_id] = $table.$this->custom_id;
				$this->used_custom_fields[$field] = true;
				$result = " $table".$this->custom_id.".AR_name = '$field' ";
				if ($nls) {
					$result = " $result and $table".$this->custom_id.".AR_nls = '$nls' ";
				}

				if (!$this->in_orderby) {
					$result = " $result and $table".$this->custom_id.".AR_value ";
				} else {
					$this->where_s_ext = $result;
					$result = " $table".$this->custom_id.".AR_value ";
				}
			break;
			case 'string':
			case 'float':
			case 'int':
				$result=$node["value"];
			break;
			case 'and':
				$cr = $this->custom_ref;
				$left=$this->compile_tree($node["left"]);
				if ($this->custom_ref > $cr) {
					$this->custom_id++;
				}

				$right=$this->compile_tree($node["right"]);
				$cr = $this->custom_ref;
				if ($this->custom_ref > $cr) {
					$this->custom_id++;
				}
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
					case '<=':
					case '>=':
					case '<':
					case '>':
						$operator=$node["operator"];
					break;
 					case '~=':
					case '=~':
						$operator="LIKE";
					break;
					case '!~':
						$operator="NOT LIKE";
					break;
					case '!*':
						$not = " not";
					case '=*':
						if ($node["left"]["id"]!=="implements") {
							$left=$this->compile_tree($node["left"]);
							$right=$this->compile_tree($node["right"]);
							/* fulltext search operators: =*, !*, =**, !** (double asterices indicate boolean mode) */
							$operator = $node["operator"];
							$result = "$not match ($left) against ('".mysqlstore::format_for_fti(substr($right,1,-1))."$boolmode') ";
							return $result;
						}
					break;
				}
				if ($node["left"]["id"]!=="implements") {
					$left=$this->compile_tree($node["left"]);
					$right=$this->compile_tree($node["right"]);
					/* lastchanged == unixtimestamp -> lastchanged == 200201.. */
					if ($node["left"]["field"]=="lastchanged") {
						$right = date("YmdHis", $right);
					}
					$result=" $left $operator $right ";
				} else {
					$type=$this->compile_tree($node["right"]);
					switch ($operator) {
						case '!=':
							/* retrieve an implements list */
							$types_tbl=$this->tbl_prefix."types";
							$query = "
									select type from $types_tbl where
									$types_tbl.implements = $type";
							$qresult = $this->store->store_run_query($query);
							while ($iresult = mysql_fetch_array($qresult)) {
								if (!$ilist) {
									$ilist = " '".$iresult['type']."' ";
								} else {
									$ilist .= ", '".$iresult['type']."' ";
								}
							}
							$result = " (".$this->tbl_prefix."objects.type not in ($ilist)) ";
						break;
						default:
							$table=$this->tbl_prefix."types";
							$this->used_tables[$table]=$table;
							$result=" (".$this->tbl_prefix."types.implements $operator $type and ".$this->tbl_prefix."objects.vtype = ".$this->tbl_prefix."types.type ) ";
						break;
					}
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
				$this->in_orderby = true;
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
					$this->limit_s=" limit ".(int)$node["offset"].", ".$node["limit"]." ";
				} else
				if ($node["offset"]) {
					$this->limit_s=" limit ".(int)$node["offset"]." ";
				} else {
					if ($this->limit) {
						$offset = (int)$this->offset;
						$this->limit_s=" limit $offset, ".(int)$this->limit." ";
					} 
				}
			break;
		}
		return $result;
	}

	// mysql specific compiler function
	function priv_sql_compile($tree) {
		$this->custom_ref = 0;
		$this->custom_id = 0;
		$this->used_tables="";
		$this->compile_tree($tree);
		$nodes=$this->tbl_prefix."nodes";
		$objects=$this->tbl_prefix."objects";
		$this->used_tables[$nodes]=$nodes;
		$this->used_tables[$objects]=$objects;
		@reset($this->used_tables);
		while (list($key, $val)=each($this->used_tables)) {
			if ($tables) {
				$tables.=", $key";
			} else {
				$tables="$key";
			}
			if ($this->select_tables[$key]) {
				$prop_dep.=" and $val.object=$objects.id ";
			}
		}

		$query="select distinct($nodes.path), $nodes.parent, $nodes.priority, ";
		$query.=" $objects.id, $objects.type, $objects.object, UNIX_TIMESTAMP($objects.lastchanged) as lastchanged, $objects.vtype ";
		$query.=" from $tables where ";
		$query.=" $nodes.object=$objects.id $prop_dep";
		$query.=" and $nodes.path like '".AddSlashes($this->path)."%' ";
		if ($this->where_s) {
			$query.=" and ( $this->where_s ) ";
		}
		if ($this->where_s_ext) {
			$query .= " and ($this->where_s_ext) ";
		}
		if ($this->orderby_s) {
			$query.= " order by $this->orderby_s, $nodes.priority DESC, $nodes.path ASC ";
		} else {
			$query.= " order by $nodes.priority DESC, $nodes.path ASC ";
		}
		$query.=" $this->limit_s ";

		return $query;
	}

  }

?>