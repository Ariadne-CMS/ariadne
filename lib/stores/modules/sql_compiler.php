<?php
  class sql_compiler {

	function parse_const(&$query) {
		/* integer or float regs[1] (& regs[2] : indicates float) */
		$reg_id.='(-?[0-9]+([.][0-9]+)?)|';

		/* single quoted string regs[3] */
		$reg_id.="([']('')*([^']|[^\\\\]['][']|\\\\')*['])|";

		/* double quoted string regs[6] */
		$reg_id.='("([^"]|\\\\\")*")';

		$reg_id.='[[:space:]]*';

		if (eregi($reg_id, $query, $regs)) {
			if (is_numeric($regs[1])) {
				if (is_numeric($regs[2])) {
					$node["id"]="float";
					$node["value"]=(float)$regs[1];
				} else {
					$node["id"]="int";
					$node["value"]=(int)$regs[1];
				}
			} else if ($str=$regs[3]) { 
				$node["id"]="string";
				$node["type"]="single";
				$node["value"]="'".substr($str, 1, -1)."'";
			} else if ($str=$regs[6]) {
				$node["id"]="string";
				$node["type"]="double";
				$node["value"]='"'.substr($str, 1, -1).'"';
			}

			$query=substr($query, strlen($regs[0]));
		} else {
			$this->error="could not find constant at '$query'";
		}
		return $node;
	}

	function parse_ident(&$query) {
		/* parse identifier regs 1,2 and 3

			reg[1]: tablename
			reg[2]: property name
			reg[3]: only used with 'my' properties
		*/
		$reg_id='^[[:space:]]*(([a-z_][a-z0-9_]*)(:[a-z]+)?([.][a-z_][a-z0-9_]*)?([.][a-z_][a-z0-9_]*)?)';
		$reg_id.='[[:space:]]*';

		if (eregi($reg_id, $query, $regs) && $regs[1]) {
			$match_1   = $regs[2];
			$record_id = substr($regs[3], 1);
			$match_2   = substr($regs[4], 1);
			$match_3   = substr($regs[5], 1);
			if (!$match_2) {
				/* default table is 'object' */
				$match_2 = $match_1;
				$match_1 = "object";
			}
			$node["id"]="ident";

			$table=$match_1;
			$field=$match_2;
			if ($table=="object") {
				switch ($field) {
					case "implements":
						$node["id"]="implements";
					break;
					case "path":
					case "parent":
					case "priority":
						$node["table"]="nodes";
						$node["field"]=$field;
					break;
					default:
						$node["table"]="objects";
						$node["field"]=$field;
				}
			} else
			if ($table == "my") {
				$node["id"] = "custom";
				if ($match_3) {
					$node["nls"] = $field;
					$field = $match_3;
				}
				$node["field"] = $field;
			} else {
				$node["table"]="prop_".$table;
				$node["field"]="AR_".$field;
				$node["record_id"] = $record_id;
			}
			$query=substr($query, strlen($regs[0]));
		} else {
			$this->error="could not find identifier at '$query'";
		}
		return $node;
	}

	function parse_cmp_expr(&$query) {
		$result=$this->parse_ident($query);
		if ($result) {
			$reg_cmp_op='^[[:space:]]*(~=|==?|\\!=|<=|>=|<|>|=~|!~)[[:space:]]*';
			if (eregi($reg_cmp_op, $query, $regs)) {
					$node["id"]="cmp";
					$node["operator"]=$regs[1];
					$node["left"]=$result;
					$query=substr($query, strlen($regs[0]));
					$result=$this->parse_const($query);
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
		$field=$this->parse_ident($query);
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
				$field=$this->parse_ident($query);
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

		if (!eregi('^[[:space:]]*order[[:space:]]*by[[:space:]]+', $query, $regs)) {
			$result=$this->parse_or_expr($query);
		} else {
			$no_selection = true;
		}
		if ($no_selection || eregi('^[[:space:]]*order[[:space:]]*by[[:space:]]+', $query, $regs)) {
			$query=substr($query, strlen($regs[0]));
			$node["id"]="orderby";
			$node["right"]=$this->parse_orderby($query);
			$node["left"]=$result;
			$result=$node;
		}
		if (eregi('^[[:space:]]*limit[[:space:]]+([0-9]+)[[:space:]]*([,][[:space:]]*([0-9]+))?', $query, $regs)) {
			$query=substr($query, strlen($regs[0]));
			$limit_s["id"]="limit";
			$limit_s["offset"]=$regs[1];
			$limit_s["limit"]=$regs[3];
		} else {
			$limit_s["id"]="limit";
			$limit_s["offset"]=($this->offset) ? $this->offset : 0;
			$limit_s["limit"]=($this->limit) ? $this->limit : 0;
		}
		$limit_s["left"]=$result;
		$result=$limit_s;

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