<?php
/******************************************************************************
  Oracle compiler 1.0b												Ariadne 2.4

  Copyright (C) 1998-2004  Muze 

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

  --------------------------------------------------------------------------

*******************************************************************************/

  include_once($this->code."stores/modules/sql_compiler.php");

  class oracle_compiler extends sql_compiler {
	function oracle_compiler(&$store, $tbl_prefix="") {
		debug("oracle_compiler($tbl_prefix)", "store");
		$this->tbl_prefix=$tbl_prefix;
		$this->store=$store;
	}


	function compile_orderby(&$node, $arguments=null) {
		if ($arguments) {
			extract($arguments);
		}
		switch ((string)$node["id"]) {
			case 'orderbyfield':
				$left=$this->compile_orderby($node["left"]);
				$right=$this->compile_orderby($node["right"]);

				if ($left) {
					$result=" $left ,  $right ".$node["type"]." ";
				} else {
					$result=" $right ".$node["type"]." ";
				}
			break;
			case 'custom':
				$table=$table_alias=$this->tbl_prefix."prop_custom";
				$nodes=$this->tbl_prefix."nodes";
				$field=$node["field"];
				$record_id=$node["record_id"];

				if ($record_id) {
					$table_alias = $alias = $table.$record_id;
				}

				if (($record_id && !$this->used_tables["$table $table_alias"]) 
						|| (!$record_id && !$this->used_tables[$table])) {

					if ($node["nls"]) {
						$nls = "and $table.AR_nls = '".$node["nls"]."' ";
					}

					$this->left_joins[] = "
						left join $table $alias on  (
							$table_alias.object = $nodes.object
							and
							$table_alias.RowId in (
								select $table.RowId
								from $table
								where $table.object = $nodes.object
								and $table.AR_name = '$field'
								$nls
								and RowNum = 1
							)
						)
					";
				}
				/*
				if ($record_id) {
					$this->used_tables["$table $table_alias"] = $table_alias;
				} else {
					$this->used_tables["$table"] = $table;
				}
				*/
				$result = "$table_alias.AR_value";
			break;
			case 'property':
				$table=$table_alias=$this->tbl_prefix.$node["table"];
				$nodes=$this->tbl_prefix."nodes";
				$field=$node["field"];
				$record_id=$node["record_id"];

				if ($record_id) {
					$table_alias = $alias = $table.$record_id;
				}
				if (($record_id && !$this->used_tables["$table $table_alias"]) 
						|| (!$record_id && !$this->used_tables[$table])) {
					$this->left_joins[] = "
						left join $table $alias on (
							$table_alias.object = $nodes.object
							and
							$table_alias.RowId in (
								select $table.RowId
								from $table
								where $table.object = $nodes.object
								and RowNum = 1
							)
						)
					";
				}
				

				$result = "$table_alias.$field";
			break;
			case 'ident':
				$table=$this->tbl_prefix.$node["table"];
				$field=$node["field"];
				$this->used_tables[$table]=$table;
				$result = "	$table.$field ";
			break;
		}
		return $result;
	}

	function compile_tree(&$node, $arguments=null) {
		if ($arguments) {
			extract($arguments);
		}
		switch ((string)$node["id"]) {
			case 'property':
				/*
					arguments:
						$operator	-> compare operator
						$value		-> value to compare
				*/
				$table=$this->tbl_prefix.$node["table"];
				$nodes=$this->tbl_prefix."nodes";
				$field=$node["field"];
				$record_id=$node["record_id"];

				if ($record_id) {
					$this->used_tables["$table $table$record_id"] = $table.$record_id;
				} else {
					$this->used_tables["$table"] = $table;
				}
				$result = "
					(
						$table$record_id.object = $nodes.object
						and
						$table$record_id.RowId in (
							select $table.RowId
							from $table
							where $table.object = $nodes.object
							and $table.$field $operator $value
							and RowNum = 1
						)
					)
				";

			break;
			case 'ident':
				/*
					arguments:
						$operator	-> compare operator
						$value		-> value to compare
				*/
				$table=$this->tbl_prefix.$node["table"];
				$field=$node["field"];
				$this->used_tables[$table]=$table;


				$result = "
					$table.$field $operator $value
				";
			break;
			case 'custom':
				$table = $this->tbl_prefix."prop_custom";
				$field = $node["field"];
				$nls = $node["nls"];
				$nodes=$this->tbl_prefix."nodes";
				$record_id=$node["record_id"];

				if ($record_id) {
					$this->used_tables["$table $table$record_id"] = $table.$record_id;
				} else {
					$this->used_tables["$table"] = $table;
				}

				if ($nls) {
					$nls_query = "and $table.AR_nls = '$nls'";
				}
				$result = "
					(
						$table$record_id.object = $nodes.object
						and
						$table$record_id.RowId in (
							select $table.RowId
							from $table
							where $table.object = $nodes.object
							and $table.AR_name = '$field'
							and $table.AR_value $operator $value
							$nls_query
							and RowNum = 1
						)
					)
				";

			break;
			case 'string':
			case 'float':
			case 'int':
				$result=$node["value"];
			break;
			case 'and':
				$left=$this->compile_tree($node["left"]);
				$right=$this->compile_tree($node["right"]);
				$result=" $left and $right ";
			break;
			case 'or':
				$left=$this->compile_tree($node["left"]);
				$right=$this->compile_tree($node["right"]);
				$result=" ($left or $right) ";
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
						$operator=$not."LIKE";
						/* double tildes indicate case-sensitive */
						if (strlen($operator)==3) {
							$operator.=" BINARY";
						}
					break;
				}
				if ($node["left"]["id"]!=="implements") {
					$right=$this->compile_tree($node["right"]);

					$result=$this->compile_tree(
								$node["left"], Array('operator' => $operator, 'value' => $right)
							);

				} else {
					$type=$this->compile_tree($node["right"]);
					switch ($operator) {
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
				$this->orderby_s=$this->compile_orderby($node["right"]);
			break;


			case 'limit':
				$this->where_s=$this->compile_tree($node["left"]);
				if ($node["limit"]) {
					$this->limit_s=" LINENUM > ".(int)$node["offset"]." and LINENUM <= ".((int)$node["offset"] + (int)$node["limit"])." ";
				} else
				if ($node["offset"]) {
					$this->limit_s=" LINENUM > ".(int)$node["offset"]." ";
				} else {
					if ($this->limit) {
						$offset = (int)$this->offset;
						$this->limit_s=" LINENUM > ".(int)$offset." and LINENUM <= ".((int)$offset + (int)$node["limit"])." ";
					} 
				}
			break;
		}
		return $result;
	}

	// oracle specific compiler function
	function priv_sql_compile($tree) {

		$this->used_tables = Array();
		$this->compile_tree($tree);

		$nodes_tbl=$this->tbl_prefix."nodes";
		$objects_tbl=$this->tbl_prefix."objects";
		$objects_tbl_data.=$objects_tbl."_data";

		$this->used_tables[$objects_tbl]=$objects_tbl;
		$this->used_tables[$nodes_tbl]=$nodes_tbl;
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

		if ($this->orderby_s) {
			$orderby_s = " order by $this->orderby_s, $nodes_tbl.parent ASC, $nodes_tbl.priority DESC, $nodes_tbl.path ASC ";
		} else {
			$orderby_s = " order by $nodes_tbl.parent ASC, $nodes_tbl.priority DESC, $nodes_tbl.path ASC ";
		}

		if ($this->where_s) {
			$where_s = "and ".$this->where_s;
		}

		if (is_array($this->left_joins)) {
			foreach($this->left_joins as $join) {
				$left_join_s .= " $join";
			}
		}

		$query = "
			select ROW_NUMBER() OVER($orderby_s) LINENUM,
					$nodes_tbl.path as \"path\", $nodes_tbl.parent as \"parent\", $nodes_tbl.priority as \"priority\", $objects_tbl.id as \"id\", $objects_tbl.type as \"type\", TO_CHAR($objects_tbl.lastchanged, 'MM-DD-YYYY HH24:MI:SS') as \"lastchanged\", $objects_tbl.vtype as \"vtype\"
			from $tables
			$left_join_s

			where $nodes_tbl.object=$objects_tbl.id $prop_dep
			and $nodes_tbl.path like '".AddSlashes($this->path)."%' 
			$where_s
			$orderby_s
			";



		if ($this->limit_s) {
			$limit_s = $this->limit_s." and";
			$limit_s_count = "where ".$this->limit_s;
		}

		$count_query = "
			select count(\"path\") as \"count\"
			from (
				$query
			) $limit_s_count
		";

		$query = "
			select \"LINENUM\", \"path\", \"parent\", \"type\", \"id\", \"priority\", \"id\", \"lastchanged\", \"vtype\", $objects_tbl_data.object as \"object\"
			from (
				$query
			), $objects_tbl_data
			where $limit_s \"id\" = $objects_tbl_data.id
		";

		return Array("query" => $query, "count_query" => $count_query);
	}

  }

?>