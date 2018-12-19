<?php
  include_once($this->code."stores/modules/mysql_compiler.php");

class mysql_workspaces_compiler extends mysql_compiler {

	public function __construct (&$store, $tbl_prefix="") {
		debug("mysql_workspaces_compiler($tbl_prefix)", "store");
		$this->tbl_prefix=$tbl_prefix;
		$this->store=$store;
	}

	// mysql specific compiler function
	protected function priv_sql_compile($tree) {
		$this->custom_ref = 0;
		$this->custom_id = 0;
		$this->used_tables=array();
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

		$join = "";
		if (is_array($this->nls_join)) {
			reset($this->nls_join);
			while (list($key, $value)=each($this->nls_join)) {
				$join .= $value;
			}
		}



		$query = " where $nodes.object=$objects.id $prop_dep";
		$query .= " and $nodes.path like '".str_replace('_','\\_',AddSlashes($this->path))."%' ";


		$query_string_layers = "";
		$layering = false;
		foreach ($this->layers as $lPath => $lId) {
			if ($lId) {
				$layering = true;
				if ($query_string_layers) {
					$query_string_layers .= " OR ";
				}
				$query_string_layers .= " ( $nodes.path like '".AddSlashes($lPath)."%' and $nodes.layer = ".((int)$lId)." ) ";
			}
		}
		if ($layering) {
			$query .= " and (
								( $query_string_layers )
							OR
								$nodes.layer = 0
								and $nodes.id NOT IN (
									select $nodes.id from $nodes where ( $query_string_layers )
								)
								and $nodes.path NOT IN (
									select $nodes.path from $nodes where ( $query_string_layers )
								)
						)
			";
		} else {
			$query .= " and $nodes.layer = 0 ";
		}

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
			$orderby = " order by $this->orderby_s, $nodes.parent ASC, $nodes.priority DESC, $nodes.path ASC ";
		} else {
			$orderby = " order by $nodes.parent ASC, $nodes.priority DESC, $nodes.path ASC ";
		}

		$select_query  = "select distinct($nodes.path), $nodes.id as nodeId, $nodes.layer as nodeLayer, $nodes.parent, $nodes.priority, ";
		$select_query .= "$objects.object, $objects.id, $objects.type, $objects.vtype, ";
		$select_query .= "UNIX_TIMESTAMP($objects.lastchanged) as lastchanged  ";
		$select_query .= "from ($tables) $join $query ";
		$select_query .= $orderby." ".$this->limit_s;

		$count_query   = "select count(distinct($objects.id)) as count from $tables ".$query;

		return array(
			"select_query" => $select_query,
			"count_query"  => $count_query
		);
	}

  }
