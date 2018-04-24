<?php
include($this->code."stores/modules/sql_compiler.php");

class postgresql_compiler extends sql_compiler {
	protected $tbl_prefix;
	protected $in_orderby;
	protected $nls_join;
	protected $select_tables;
	protected $used_tables;
	protected $custom_id;
	protected $custom_ref;
	protected $used_custom_fields;
	protected $where_s_ext;
	protected $where_s;
	protected $limit_s;
	protected $orderby_s;
	protected $select_list;

	function __construct(&$store, $tbl_prefix="") {
		debug("postgresql_compiler($tbl_prefix)", "store");
		$this->tbl_prefix=$tbl_prefix;
		$this->store=$store;
	}

	function test_for_lowercase(&$node){
		$ret = false;
		switch ((string)$node["id"]) {
			case 'ident':
				if ( $node["table"] == "nodes" ) {
					if ( $node["field"] == "path" or $node["field"] == "parent" ) {
						$ret = true;
					}
				}
				break;
		}
		return $ret;
	}

	function compile_tree(&$node) {
		switch ((string)$node["id"]) {
			case 'property':
				$table=$this->tbl_prefix.$node["table"];
				$field=$node["field"];
				$record_id=$node["record_id"];
				if (!$record_id) {
					if (!$this->in_orderby) {
						$result=" $table.object = ".$this->tbl_prefix."objects.id and $table.$field ";
						$this->used_tables[$table]=$table;
					} else {
						if ($this->in_orderby && $node["nls"]) {
							/*
								we do a left join so that we will also find non
								matching objects
							*/
							$objects_table = $this->tbl_prefix."objects";
							$this->nls_join[$table] = "left join $table as order_$table on $objects_table.id=order_$table.object and order_$table.AR_nls='".$node["nls"]."' ";

							$result = " order_$table.$field ";
							$this->select_list["order_".$table.".".$field] = "order_$table.$field";

						} else {
							/*
								if we are parsing 'orderby' properties we have
								to join our tables for the whole query
							*/
							$this->select_tables[$table]=$table;
							$this->used_tables[$table]=$table;
							$result=" $table.$field ";
						}
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
					if (!$this->in_orderby ) {
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
				$result=" '".pg_escape_string($node["value"])."' ";
			break;
			case 'float':
			case 'int':
				$result=" ".$node["value"]." ";
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
				$not = '';
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
						if (!strlen($node["operator"])==3) {
							$not.="I";
						}
						$operator=$not."LIKE";
					break;
					case '!/':
					case '!//':
						$not="!";
					case '=/':
					case '=//':
						$operator=$not."~";
						if (strlen($node["operator"])==3) {
							$operator.="*";
						}
						break;
				}
				if ($node["left"]["id"]!=="implements") {
					$left=$this->compile_tree($node["left"]);
					$right=$this->compile_tree($node["right"]);
					if($this->test_for_lowercase($node["left"])){
						// lowercase compile
						$result=" lower($left) $operator lower($right) ";
					} else {
						// normal compile
						/* lastchanged == unixtimestamp -> lastchanged == 200201.. */
						if ($node["left"]["field"]=="lastchanged") {
							$right = "to_timestamp($right)";
						}
						$result=" $left $operator $right ";
					}
				} else {
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
				if ( $node["right"]["field"] != "none" ) {
					$right=$this->compile_tree($node["right"]);
					if ($left) {
						$result=" $left ,  $right ".$node["type"]." ";
						if($node["left"]['id'] == 'property' && !$node["right"]['nls']){
							$lefttablefield = $this->tbl_prefix.$node["left"]['table'].".".$node["left"]['field'];
							$this->select_list[$lefttablefield] = $lefttablefield;
						}
					} else {
						$result=" $right ".$node["type"]." ";
					}
					if($node["right"]['id'] == 'property' && !$node["right"]['nls']){
						$righttablefield = $this->tbl_prefix.$node["right"]['table'].".".$node["right"]['field'];
						$this->select_list[$righttablefield] = $righttablefield;
					}
				} else {
					$result = "";
					if ($left) {
						$result = " $left ";
						if($node["left"]['id'] == 'property' && !$node["right"]['nls']){
							$lefttablefield = $this->tbl_prefix.$node["left"]['table'].".".$node["left"]['field'];
							$this->select_list[$lefttablefield] = $lefttablefield;
						}
					}
					$this->skipDefaultOrderBy = true;
				}
			break;

			case 'limit':
				$this->where_s=$this->compile_tree($node["left"]);
				$this->limit_s="";
				if ($node["limit"]) {
					$this->limit_s=" offset ".(int)$node["offset"]." limit ".$node["limit"]." ";
				} else
				if ($node["offset"]) {
					$this->limit_s=" limit ".(int)$node["offset"]." ";
				} else {
					if ($this->limit) {
						$offset = (int)$this->offset;
						$this->limit_s=" offset $offset limit ".(int)$this->limit." ";
					}
				}
			break;
		}
		return $result;
	}

	// postgresql specific compiler function
	function priv_sql_compile($tree) {
		$this->custom_ref = 0;
		$this->custom_id = 0;
		$this->used_tables=array();
		$this->compile_tree($tree);

		if ( $this->error ) {
			return null;
		}

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
		if (is_array($this->nls_join)) {
			$join = join($this->nls_join);
		}
		if(is_array($this->select_list)){
			$select = join(", ", $this->select_list);
		}

		$query ="where $nodes.object=$objects.id $prop_dep";
		$query.=" and $nodes.path like '".str_replace('_','\\_',pg_escape_string($this->path))."%' ";
		if ($this->where_s) {
			$query.=" and ( $this->where_s ) ";
		}
		if ($this->where_s_ext) {
			$query .= " and ($this->where_s_ext) ";
		}
		$order = '';
		if ($this->orderby_s) {
			if ($this->skipDefaultOrderBy) {
				$order .= "order by $this->orderby_s";
			} else {
				$order .= "order by $this->orderby_s, $nodes.parent ASC, $nodes.priority DESC, $nodes.path ASC ";
			}
		} else if (!$this->skipDefaultOrderBy) {
			$order .= "order by $nodes.parent ASC, $nodes.priority DESC, $nodes.path ASC ";
		}

		$order .= " $this->limit_s ";

		$select_query  = "select distinct($nodes.path), $nodes.parent, $nodes.priority, ";
		$select_query .= " $objects.id, $objects.type, $objects.object, date_part('epoch', $objects.lastchanged) as lastchanged, $objects.vtype ";

		if($select){
			$select_query .= ", " . $select;
		}

		$select_query .= " from $tables $join $query $order";
		$count_query = "select count(distinct($nodes.path)) as count from $tables ".$query;

		return Array("select_query" => $select_query, "count_query" => $count_query);
	}

  }
