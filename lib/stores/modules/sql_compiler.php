<?php
  class sql_compiler {
	function parse_term(&$query, $type="") {

		// parse identifier regs[1] (& regs[2] : field_id)
		if ($type!=="const") {
			$regsoffset=0;
			$reg_id='^[[:space:]]*([a-z_][a-z0-9_]*([.][a-z_][a-z0-9_]*)?)';
		}
		if ($type!=="ident") {
			if ($reg_id) {
				$reg_id.="|";
			}
			$regsoffset=-2;
			// integer or float regs[3] (& regs[4] : indicates float)
			$reg_id.='(-?[0-9]+([.][0-9]+)?)|';
			// single quoted string regs[5]
			$reg_id.='([\']([^\']|\\\\\')*[\'])|';
			// double quoted string regs[7]
			$reg_id.='("([^"]|\\\\\")*")';
		}
		$reg_id.='[[:space:]]*';

		if (eregi($reg_id, $query, $regs)) {
			if ($type!=="const" && $regs[1]) {
				$node["id"]="ident";
				if (!$regs[2]) {
					if ($regs[1]==="implements") {
						$node["id"]="implements";
					} else
					if ($regs[1]==="path" || $regs[1]==="parent" || $regs[1]==="priority") {
						$node["table"]="nodes";
						$node["field"]=$regs[1];
					} else {
						$node["table"]="objects";
						$node["field"]=$regs[1];
					}
				} else {
					$table=substr($regs[1],0,-strlen($regs[2]));
					$field=substr($regs[2],1);

					if ($table=="object") {
						if ($field==="implements") {
							$node["id"]="implements";
						} else
						if ($field==="path" || $field==="parent" || $field==="priority") {
							$node["table"]="nodes";
							$node["field"]=$field;
						} else {
							$node["table"]="objects";
							$node["field"]=$field;
						}
					} else {
						$node["table"]="prop_".$table;
						$node["field"]="AR_".$field;
					}
				}
			} else 
			if (is_numeric($regs[3+$regsoffset])) {
				if (is_numeric($regs[4+$regsoffset])) {
					$node["id"]="float";
					$node["value"]=(float)$regs[3+$regsoffset];
				} else {
					$node["id"]="int";
					$node["value"]=(int)$regs[3+$regsoffset];
				}
			} else if (($str=$regs[5+$regsoffset]) || ($str=$regs[7+$regsoffset])) {
				$node["id"]="string";
				$node["value"]="'".substr($str, 1, -1)."'";
			}

			$query=substr($query, strlen($regs[0]));
		} else {
			$this->error="could not find identifier at '$query'";
		}
		return $node;
	}

	function parse_cmp_expr(&$query) {
		$result=$this->parse_term($query);
		if ($result) {
			$reg_cmp_op='^[[:space:]]*(~=|==?|\\!=|<=|>=|<|>|=~|!~)[[:space:]]*';
			if (eregi($reg_cmp_op, $query, $regs)) {
					$node["id"]="cmp";
					$node["operator"]=$regs[1];
					$node["left"]=$result;
					$query=substr($query, strlen($regs[0]));
					$result=$this->parse_term($query, "const");
					if ($result) {
						$node["right"]=$result;
					}
					$result=$node;
			} else {
				$this->error="unknow compare-operator near '$query'";
			}
		}
		return $result;
	}

	function parse_group_expr(&$query) {
		if (eregi('^[[:space:]]*([(])[[:space:]]*', $query, $regs)) {
			$query=substr($query, strlen($regs[0]));
			$result=$this->parse_or_expr($query);
			if (eregi('^[[:space:]]*([)])', $query, $regs)) {
				$query=substr($query, strlen($regs[0]));
				$node["id"]="group";
				$node["left"]=$result;
				$result=$node;
			} else {
				unset($result);
				$this->error="missing closing group sign near '$query'";
			}
		} else {
			$result=$this->parse_cmp_expr($query);
		}
		return $result;
	}

	function parse_and_expr(&$query) {
		$result=$this->parse_group_expr($query);
		while ($result && eregi('^[[:space:]]*(and)', $query, $regs) && $regs[1]) {
			$query=substr($query, strlen($regs[0]));
			$right=$this->parse_group_expr($query);
			if ($right) {
				unset($node);
				$node["id"]="and";
				$node["left"]=$result;
				$node["right"]=$right; 
				$result=$node;
			} else {
				unset($result);
			}
		}
		return $result;
	}

	function parse_or_expr(&$query) {
		$result=$this->parse_and_expr($query);
		while ($result && eregi('^[[:space:]]+(or)[[:space:]]+', $query, $regs)) {
			$query=substr($query, strlen($regs[0]));
			$right=$this->parse_and_expr($query);
			if ($right) {
				unset($node);
				$node["id"]="or";
				$node["left"]=$result;
				$node["right"]=$right; 
				$result=$node;
			} else {
				unset($result);
			}
		}

		return $result;
	}

	function parse_orderby(&$query) {
		$field=$this->parse_term($query, "ident");
		$reg_sort_type='^[[:space:]]*(ASC|DESC)';
		if (eregi($reg_sort_type, $query, $regs)) {
			$sort_type=$regs[1];
			$query=substr($query, strlen($regs[0]));
		} else {
			$sort_type="ASC";	// default
		}
		while ($field) {
			$node["id"]="orderbyfield";
			if ($sort_type) {
				$node["type"]=$sort_type;
				$node["right"]=$field;
				$node["left"]=$result;
				$result=$node;
				$sort_type="";
			}
			if (eregi('^[[:space:]]*[,]', $query, $regs)) {
				$query=substr($query, strlen($regs[0]));
				$field=$this->parse_term($query, 1);
				$reg_sort_type='[[:space:]](ASC|DESC)';
				if (eregi($reg_sort_type, $query, $regs)) {
					$sort_type=$regs[1];
					$query=substr($query, strlen($regs[0]));
				} else {
					$sort_type="ASC";	// default
				}
			} else {
				unset($field);
			}
		}
		return $result;
	}

	function parse_query(&$query) {
		$result=$this->parse_or_expr($query);
		if ($result) {
			if (eregi('^[[:space:]]+order[[:space:]]*by[[:space:]]+', $query, $regs)) {
				$query=substr($query, strlen($regs[0]));
				$node["id"]="orderby";
				$node["right"]=$this->parse_orderby($query);
				$node["left"]=$result;
				$result=$node;
			}
			if (eregi('^[[:space:]]+limit[[:space:]]+([0-9]+)[[:space:]]*([,][[:space:]]*([0-9]+))?', $query, $regs)) {
				$query=substr($query, strlen($regs[0]));
				$limit_s["id"]="limit";
				$limit_s["offset"]=$regs[1];
				$limit_s["limit"]=($regs[3]) ? $regs[3] : $this->limit;
			} else {
				$limit_s["id"]="limit";
				$limit_s["offset"]=($this->offset) ? $this->offset : 0;
				$limit_s["limit"]=($this->limit) ? $this->limit : 0;
			}
			$limit_s["left"]=$result;
			$result=$limit_s;
		}
		return $result;
	}

	// virtual (&private) method. To be implemented in the sql specific compiler
	function priv_sql_compile($node) {
	}

	function compile($query, $limit=100, $offset=0) {
		debug("sql_compiler::compile ($query, $limit, $offset)", "store");
		$this->error="";
		$compiled_query=$this->cache[$query];

		$this->limit=$limit;
		$this->offset=$offset;

		if (!$compiled_query) {
			$cache_query=$query;
			$tree=$this->parse_query($query);
			if (!$this->error && trim($query)) {
				$this->error="unkown operator near '$query'";
				$result="";
			} else {
				if ($tree) {
					$compiled_query=$this->priv_sql_compile($tree);
					$this->cache[$cache_query]=$compiled_query;
				}
			}
		}
		return $compiled_query;
	}


  }
?>