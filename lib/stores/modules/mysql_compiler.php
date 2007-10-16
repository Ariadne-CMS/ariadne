<?php
  include_once($this->code."stores/modules/sql_compiler.php");

  class mysql_compiler extends sql_compiler {
	function mysql_compiler(&$store, $tbl_prefix="") {
		debug("mysql_compiler($tbl_prefix)", "store");
		$this->tbl_prefix=$tbl_prefix;
		$this->store=$store;
	}

	function compile_tree(&$node, $arguments=null) {
		if ($arguments) {
			extract($arguments);
		}
		switch ((string)$node["id"]) {
			case 'property':
				$table=$this->tbl_prefix.$node["table"];
				$field=$node["field"];
				$record_id=$node["record_id"];
				if (!$record_id) {
					if ($this->in_orderby && $node["nls"]) {
						/* 
							we do a left join so that we will also find non
							matching objects
						*/
						$objects_table = $this->tbl_prefix."objects";
						$aliastable = $table.$node['nls'];
						$this->nls_join[$table.$node['nls']] = "left join $table as order_$aliastable on $objects_table.id=order_$aliastable.object and order_$aliastable.AR_nls='".$node["nls"]."' ";
						$result = " order_$aliastable.$field ";
					} else {
						/*
							if we are parsing 'orderby' properties we have 
							to join our tables for the whole query
						*/							
						$this->select_tables[$table]=$table;
						$this->used_tables[$table]=$table;
						$result=" $table.$field ";
					}
				} else {
					$this->used_tables["$table as $table$record_id"] = $table.$record_id;
					if (!$this->in_orderby && !$no_context_join) {
						if ($this->join_target_properties[$node["table"]][":$record_id"]) {
							$result=" $table$record_id.object = target.object and $table$record_id.$field "; 
						} else {
							$result=" $table$record_id.object = ".$this->tbl_prefix."objects.id and $table$record_id.$field ";
						}
					} else {
						if ($this->join_target_properties[$node["table"]]) {
							$this->join_target_properties["$table as $table$record_id"] = $table.$record_id;
						}
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
				$record_id = $node["record_id"];
				/*
					when we are compiling orderby properties we always want
					to assign it to a new table alias
				*/
				if ($this->in_orderby) {
					$this->custom_id++;
				}
				$this->custom_ref++;
				if (!$record_id) {
					$this->used_tables[$table." as $table".$this->custom_id] = $table.$this->custom_id;
					$this->select_tables[$table." as $table".$this->custom_id] = 1;

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
				} else {
					$this->used_tables["$table as $table$record_id"] = $table.$record_id;
			//		$this->select_tables[$table." as $table$record_id"] = 1;

					$result = " $table$record_id.AR_name = '$field' ";
					if (!$this->in_orderby && !$no_context_join) {
						if ($this->join_target_properties["prop_my"][":$record_id"]) {
							$result=" $result and $table$record_id.object = target.object and $table$record_id.AR_value "; 
						} else {
							$result=" $table$record_id.object = ".$this->tbl_prefix."objects.id and $table$record_id.AR_value ";
						}
					} else {
						if ($this->join_target_properties[$node["table"]]) {
							$this->join_target_properties["$table as $table$record_id"] = $table.$record_id;
						}
						$this->select_tables["$table as $table$record_id"] = $table.$record_id;
						$result=" $table$record_id.AR_value ";
					}
				}
			break;
			case 'string':
				$result = $node["value"];
				if ($escape_chars) {
					$result = ereg_replace('([^\\])_', '\\1\_', $result);
				}
				return $result;
			break;
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
				$not="";
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
					case '!~':
					case '!~~':
						$not="NOT ";
 					case '~=':
					case '=~':
					case '=~~':
						$likeOp = true;
						$operator=$not."LIKE";
						/* double tildes indicate case-sensitive */
						if (strlen($operator)==3) {
							$operator.=" BINARY";
						}
					break;
					case '!/':
					case '!//':
						$not="NOT ";
					case '=/':
					case '=//':
						$operator=$not."REGEXP";
						/* double slashes indicate case-sensitive */
						if (strlen($operator)==3) {
							$operator.=" BINARY";
						}
					break;
					case '!*':
					case '!**':
						$not = " not";
					case '=*':
					case '=**':
						if ($node["left"]["id"]!=="implements" && $this->store->is_supported("fulltext")) {
							$left=$this->compile_tree($node["left"], Array("no_context_join" => true));
							$right=$this->compile_tree($node["right"]);
							/* fulltext search operators: =*, !*, =**, !** */
							$operator = $node["operator"];
							$query = stripslashes(substr($right,1,-1));
							if (strlen($operator)==3 && $this->store->is_supported("fulltext_boolean")) {
								/* double asterisks indicate boolean mode */
								/* make sure the operators are not formatted_for_fti */
								$storeclass=get_class($this->store);
								$query = preg_replace(
										'%(^|\s)([-+~<>(]*)("([^"]*)"|([^ "*]*))([)*]?)%e',
										"'\\1\\2'.('\\4'?'\"'.$storeclass::format_for_fti('\\4').'\"':$storeclass::format_for_fti('\\5')).'\\6'",
										$query);
								$boolmode = " in boolean mode";
							} else {
								$boolmode = "";
								$query = $this->store->format_for_fti($query);
							}
							$result = "$not match ($left) against ('$query'$boolmode) ";
							$this->fulltext_expr[':'.$node["right"]["record_id"]] = $result;
							return $result;
						}
					break;
				}
				if ($node["left"]["id"]!=="implements") {
					$left=$this->compile_tree($node["left"]);
					if ($likeOp) {
						$right=$this->compile_tree($node["right"], Array('escape_chars' => true));
					} else {
						$right=$this->compile_tree($node["right"]);
					}
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
				if ($node["right"]["field"] == 'AR_relevance' && $this->store->is_supported("fulltext")) {
					$right = $this->fulltext_expr[':'.$node["right"]["record_id"]];
				} else {
					$right=$this->compile_tree($node["right"]);
				}
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
		$properties=$this->tbl_prefix."prop_";
		$this->used_tables[$nodes]=$nodes;
		$this->used_tables[$objects]=$objects;
		if ($this->join_target_properties) {
			$this->used_tables[$properties."references as target_reference"] = $properties."references as target_reference";
			$this->used_tables["$nodes as target"] = "$nodes as target";
		}		
		@reset($this->used_tables);
		while (list($key, $val)=each($this->used_tables)) {
			if ($tables) {
				$tables.=", $key";
			} else {
				$tables="$key";
			}
			if ($this->select_tables[$key]) {
				if ($this->join_target_properties[$key]) {
					$prop_dep.=" and $val.object=target.object ";
				} else {
					$prop_dep.=" and $val.object=$objects.id ";
				}
			}
		}

		if (is_array($this->nls_join)) {
			reset($this->nls_join);
			while (list($key, $value)=each($this->nls_join)) {
				$join .= $value;
			}
		}


		$query="select distinct($nodes.path), $nodes.parent, $nodes.priority, ";
		$query.=" $objects.id, $objects.type, $objects.object, UNIX_TIMESTAMP($objects.lastchanged) as lastchanged, $objects.vtype ";
		$query.=" from $tables $join";
		$query.=" where $nodes.object=$objects.id $prop_dep";
		$query.=" and $nodes.path like '".AddSlashes($this->path)."%' ";
		if ($this->where_s) {
			$query.=" and ( $this->where_s ) ";
		}
		if ($this->where_s_ext) {
			$query .= " and ($this->where_s_ext) ";
		}
		/* do target join */
		if ($this->join_target_properties) {
			$query .= " and $objects.id = target_reference.object ";
			$query .= " and target.path = target_reference.AR_path ";
		}

		if ($this->orderby_s) {
			$query.= " order by $this->orderby_s, $nodes.parent ASC, $nodes.priority DESC, $nodes.path ASC ";
		} else {
			$query.= " order by $nodes.parent ASC, $nodes.priority DESC, $nodes.path ASC ";
		}
		$query.=" $this->limit_s ";
		return $query;
	}

  }

?>