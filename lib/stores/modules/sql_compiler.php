<?php

abstract class sql_compiler {
	protected $store;
	public  $error;
	protected $join_target_properties;
	protected $offset;
	protected $limit;
	protected $cache;
	protected $path;
	protected $_SCAN_WS        = Array(" " => true, "\t" => true, "\n" => true ,"\r" => true);
	protected $_SCAN_AZ        = Array("a" => true, "A" => true, "b" => true, "B" => true, "c" => true, "C" => true, "d" => true, "D" => true, "e" => true, "E" => true, "f" => true, "F" => true, "g" => true, "G" => true, "h" => true, "H" => true, "i" => true, "I" => true, "j" => true, "J" => true, "k" => true, "K" => true, "l" => true, "L" => true, "m" => true, "M" => true, "n" => true, "N" => true, "o" => true, "O" => true, "p" => true, "P" => true, "q" => true, "Q" => true, "r" => true, "R" => true, "s" => true, "S" => true, "t" => true, "T" => true, "u" => true, "U" => true, "v" => true, "V" => true, "w" => true, "W" => true, "x" => true, "X" => true, "y" => true, "Y" => true, "z" => true, "Z" => true);
	protected $_SCAN_AZ_09     = Array("a" => true, "A" => true, "b" => true, "B" => true, "c" => true, "C" => true, "d" => true, "D" => true, "e" => true, "E" => true, "f" => true, "F" => true, "g" => true, "G" => true, "h" => true, "H" => true, "i" => true, "I" => true, "j" => true, "J" => true, "k" => true, "K" => true, "l" => true, "L" => true, "m" => true, "M" => true, "n" => true, "N" => true, "o" => true, "O" => true, "p" => true, "P" => true, "q" => true, "Q" => true, "r" => true, "R" => true, "s" => true, "S" => true, "t" => true, "T" => true, "u" => true, "U" => true, "v" => true, "V" => true, "w" => true, "W" => true, "x" => true, "X" => true, "y" => true, "Y" => true, "z" => true, "Z" => true, "_" => true, "0" => true, "1" => true, "2" => true, "3" => true, "4" => true, "5" => true, "6" => true, "7" => true, "8" => true, "9" => true);
	protected $_SCAN_NUM       = Array("0" => true, "1" => true, "2" => true, "3" => true, "4" => true, "5" => true, "6" => true, "7" => true, "8" => true, "9" => true);
	protected $_SCAN_NUM_START = Array("0" => true, "1" => true, "2" => true, "3" => true, "4" => true, "5" => true, "6" => true, "7" => true, "8" => true, "9" => true, "-" => true);
	protected $_SCAN_CMP       = Array("~" => Array("=" => Array("FIN" => true)), "=" => Array("=" => Array("FIN" => true), "FIN" => true, "~" => Array("FIN" => true, "~" => Array("FIN" => true)), "*" => Array("FIN" => true, "*" => Array("FIN" => true)), "/" => Array("FIN" => true)), "!" => Array("=" => Array("FIN" => true), "~" => Array("FIN" => true, "~" => Array("FIN" => true)), "*" => Array("FIN" => true, "*" => Array("FIN" => true)), "/" => Array("FIN" => true, "/" => Array("FIN" => true))), "<" => Array("=" => Array("FIN" => true), "FIN" => true), ">" => Array("=" => Array("FIN" => true), "FIN" => true), "/" => Array("=" => Array("=" => Array("FIN" => true))));


	protected function parse_const(&$YYBUFFER) {
		$YYCURSOR = 0;
		while (isset($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]])) {
			$YYCURSOR++;
		}
		$value = '';
		$yych = $YYBUFFER[$YYCURSOR];
		switch (true) {
			case '"' === $yych: 
			case "'" === $yych:
				$quote = $yych;
				$yych = $YYBUFFER[++$YYCURSOR];
				while ($yych !== "\0" && $yych !== $quote) {
					if ($yych === "\\") {
						$yych = $YYBUFFER[++$YYCURSOR];
						if ($yych !== $quote && $yych != "\\") {
							$value .= "\\";
						}
					}
					$value .= $yych;
					$yych = $YYBUFFER[++$YYCURSOR];
				}
				$YYBUFFER = substr($YYBUFFER, $YYCURSOR + 1);
				$node["id"] = "string";
				$node["type"] = ($quote === '"') ? "double" : "single";
				$node["value"] = "'".AddSlashes($value)."'";
				return $node;
			break;
			case $this->_SCAN_NUM_START[$yych]:
				$value = $yych;
				$yych = $YYBUFFER[++$YYCURSOR];
				while (isset($this->_SCAN_NUM[$yych])) {
					$value .= $yych;
					$yych = $YYBUFFER[++$YYCURSOR];
				}
				if ($yych === '.') {
					$value .= $yych;
					$yych = $YYBUFFER[++$YYCURSOR];
					while (isset($this->_SCAN_NUM[$yych])) {
						$value .= $yych;
						$yych = $YYBUFFER[++$YYCURSOR];
					}
					$node["id"]="float";
					$node["value"]=(float)$value;
				} else {
					$node["id"]="int";
					$node["value"]=(int)$value;;
				}
				$YYBUFFER = substr($YYBUFFER, $YYCURSOR);
				return $node;
			break;
		}
	}

	protected function parse_ident(&$YYBUFFER) {
		/* parse identifier regs 1,2 and 3

			reg[1]: tablename
			reg[2]: property name
			reg[3]: only used with 'my' properties
		*/
		$reg_id='^[[:space:]]*(([a-z_][a-z0-9_]*)(:[a-z]+)?([.][a-z_][a-z0-9_]*)?([.][a-z_][a-z0-9_]*)?)';
		$reg_id.='[[:space:]]*';

		$YYCURSOR = 0;
		while (isset($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]])) {
			$YYCURSOR++;
		}
		$value = '';
		$yych = $YYBUFFER[$YYCURSOR];

		if ($this->_SCAN_AZ[$yych]) {
			$value .= $yych;
			$yych = $YYBUFFER[++$YYCURSOR];
			while (isset($this->_SCAN_AZ_09[$yych])) {
				$value .= $yych;
				$yych = $YYBUFFER[++$YYCURSOR];
			}
			$match_1 = $value; $value = '';
			if ($yych === ':') {
				$yych = $YYBUFFER[++$YYCURSOR];
				while (isset($this->_SCAN_AZ[$yych])) {
					$value .= $yych;
					$yych = $YYBUFFER[++$YYCURSOR];
				}
				$record_id = $value; $value = '';
			}
			if ($yych === '.') {
				$yych = $YYBUFFER[++$YYCURSOR];
				if ($this->_SCAN_AZ[$yych]) {
					$value .= $yych;
					$yych = $YYBUFFER[++$YYCURSOR];
					while (isset($this->_SCAN_AZ_09[$yych])) {
						$value .= $yych;
						$yych = $YYBUFFER[++$YYCURSOR];
					}
				}
				$match_2 = $value; $value = '';
			}
			if ($yych === '.') {
				$yych = $YYBUFFER[++$YYCURSOR];
				if ($this->_SCAN_AZ[$yych]) {
					$value .= $yych;
					$yych = $YYBUFFER[++$YYCURSOR];
					while (isset($this->_SCAN_AZ_09[$yych])) {
						$value .= $yych;
						$yych = $YYBUFFER[++$YYCURSOR];
					}
				}
				$match_3 = $value; $value = '';
			}

		}


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
		if ($table === "my") {
			$node["id"] = "custom";
			if ($match_3) {
				$node["nls"] = $field;
				$field = $match_3;
			}
			$node["field"] = $field;
			$node["record_id"] = $record_id;
		} else {
			$node["id"]="property";
			if ($match_3) {
				$node["nls"] = $field;
				$field = $match_3;
			}
			$node["table"]="prop_".$table;
			$node["field"]="AR_".$field;
			$node["record_id"] = $record_id;
		}
		$YYBUFFER = substr($YYBUFFER, $YYCURSOR);
		return $node;
	}

	protected function parse_cmp_expr(&$YYBUFFER) {
		$result=$this->parse_ident($YYBUFFER);
		if ($result) {
			$YYCURSOR = 0;
			while (isset($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]])) {
				$YYCURSOR++;
			}
			$yych = $YYBUFFER[$YYCURSOR];
			$YYCURSOR_START = $YYCURSOR;
			$RULES = &$this->_SCAN_CMP;
			while (isset($RULES[$yych])) {
				$RULES = &$RULES[$yych];
				if (isset($RULES['FIN'])) {
					$YYMATCH = $YYCURSOR;
				}
				$yych = $YYBUFFER[++$YYCURSOR];
			}
			if (isset($YYMATCH)) {
					$node["id"]="cmp";
					$node["operator"]=substr($YYBUFFER, $YYCURSOR_START, ($YYMATCH + 1) - $YYCURSOR_START);
					$node["left"]=$result;
					$YYBUFFER = substr($YYBUFFER, $YYCURSOR);
					$result=$this->parse_const($YYBUFFER);
					if ($result) {
						$node["right"]=$result;
					}
					$result=$node;
			} else {
				$this->error="unknow compare-operator near '$YYBUFFER'";
			}
		}
		return $result;
	}

	protected function parse_group_expr(&$YYBUFFER) {
		$YYCURSOR = 0;
		while (isset($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]])) {
			$YYCURSOR++;
		}
		$yych = $YYBUFFER[$YYCURSOR++];
		if ($yych === '(') {
			$YYBUFFER = substr($YYBUFFER, $YYCURSOR);
			$result = $this->parse_or_expr($YYBUFFER);
			$YYCURSOR = 0;
			while (isset($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]])) {
				$YYCURSOR++;
			}
			$yych = $YYBUFFER[$YYCURSOR++];
			if ($yych === ')') {
				$YYBUFFER = substr($YYBUFFER, $YYCURSOR);
				$node["id"]="group";
				$node["left"]=$result;
				$result=$node;
			} else {
				unset($result);
				$this->error = "missing closing group sign near '$YYBUFFER'";
			}
		} else {
			$result = $this->parse_cmp_expr($YYBUFFER);
		}
		return $result;
	}

	protected function parse_and_expr(&$YYBUFFER) {
		$result=$this->parse_group_expr($YYBUFFER);
		while (is_array($result)) {
			$YYCURSOR = 0;
			while (isset($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]])) {
				$YYCURSOR++;
			}
			$ident = strtolower(substr($YYBUFFER, $YYCURSOR, 3));
			if ($ident === 'and' && !isset($this->_SCAN_AZ_09[$YYBUFFER[$YYCURSOR + 3]]) ) {
				$YYBUFFER = substr($YYBUFFER, $YYCURSOR + 3);
				$right = $this->parse_group_expr($YYBUFFER);
				if (is_array($right)) {
					$result = Array(
						'id' => $ident,
						'left' => $result,
						'right' => $right
					);
				} else {
					unset($result);
				}
			} else {
				break;
			}
		}
		return $result;
	}

	protected function parse_or_expr(&$YYBUFFER) {
		$result=$this->parse_and_expr($YYBUFFER);
		while (is_array($result)) {
			$YYCURSOR = 0;
			while (isset($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]])) {
				$YYCURSOR++;
			}
			$ident = strtolower(substr($YYBUFFER, $YYCURSOR, 2));
			if ($ident === 'or' && !isset($this->_SCAN_AZ_09[$YYBUFFER[$YYCURSOR + 2]]) ) {
				$YYBUFFER = substr($YYBUFFER, $YYCURSOR + 2);
				$right = $this->parse_and_expr($YYBUFFER);
				if (is_array($right)) {
					$result = Array(
						'id' => $ident,
						'left' => $result,
						'right' => $right
					);
				} else {
					unset($result);
				}
			} else {
				break;
			}
		}
		return $result;
	}

	protected function parse_orderby(&$YYBUFFER) {
		$field = $this->parse_ident($YYBUFFER);
		$reg_sort_type = '^[[:space:]]*(ASC|DESC)';

		$YYCURSOR = 0;
		while (isset($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]])) {
			$YYCURSOR++;
		}
		$value = '';
		$yych  = $YYBUFFER[$YYCURSOR];
		if ($this->_SCAN_AZ[$yych]) {
			$value .= $yych;
			$yych = $YYBUFFER[++$YYCURSOR];
			while (isset($this->_SCAN_AZ[$yych])) {
				$value .= $yych;
				$yych = $YYBUFFER[++$YYCURSOR];
			}
			$sort_type = strtoupper($value);
			if (!($sort_type == 'ASC' || $sort_type == 'DESC')) { // If sort type is anything else than ASC or DESC, it is not part of the order by.
				$sort_type = 'ASC';
				$YYCURSOR = $YYCURSOR - strlen($value);
				$value = '';
			}
		} else {
			$sort_type = 'ASC';
		}
		while (is_array($field)) {
			$result = Array(
				'id' => 'orderbyfield',
				'type' => $sort_type,
				'right' => $field,
				'left' => $result
			);
			while (isset($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]])) {
				$YYCURSOR++;
			}
			$yych  = $YYBUFFER[$YYCURSOR];
			if ($yych !== ',') {
				$YYBUFFER = substr($YYBUFFER, $YYCURSOR);
				unset($field);
			} else {
				$YYBUFFER = substr($YYBUFFER, $YYCURSOR + 1);
				$field = $this->parse_ident($YYBUFFER);
				$YYCURSOR = 0;
				while (isset($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]])) {
					$YYCURSOR++;
				}
				$value = '';
				$yych  = $YYBUFFER[$YYCURSOR];
				if ($this->_SCAN_AZ[$yych]) {
					$value .= $yych;
					$yych = $YYBUFFER[++$YYCURSOR];
					while (isset($this->_SCAN_AZ[$yych])) {
						$value .= $yych;
						$yych = $YYBUFFER[++$YYCURSOR];
					}
					$sort_type = strtoupper($value);
					if (!($sort_type == 'ASC' || $sort_type == 'DESC')) { // If sort type is anything else than ASC or DESC, it is not part of the order by.
						$sort_type = 'ASC';
						$YYCURSOR = $YYCURSOR - strlen($value);
						$value = '';
					}
				} else {
					$sort_type = 'ASC';
				}
			}
		}
		return $result;
	}


	protected function parse_join_target_properties(&$query) {
		do {
			if (!preg_match('/^([a-z_][a-z0-9_]*)(:[a-z]+)?/i', $query, $regs)) {
				$this->error = "expected property name at '$query'";
				return false;
			}
			$this->join_target_properties["prop_".$regs[1]][$regs[2]] = true;
			$query = substr($query, strlen($regs[0]));

			if (!preg_match('/^[[:space:]]*,[[:space:]]*/', $query, $regs)) {
				return true;
			}
			$query = substr($query, strlen($regs[0]));
		} while(1);
	}

	protected function parse_query(&$query) {

		if (!preg_match('|^[[:space:]]*order[[:space:]]*by[[:space:]]+|i', $query, $regs)) {
			$result=$this->parse_or_expr($query);
		} else {
			$no_selection = true;
		}

/*
		$YYCURSOR = 0;
		while ($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]]) {
			$YYCURSOR++;
		}

		$yych  = $YYBUFFER[$YYCURSOR];
		if ($this->_SCAN_AZ[$yych]) {
			$value = $yych;
			$yych  = $YYBUFFER[++$YYCURSOR];
			while ($this->_SCAN_AZ[$yych]) {
				$value .= $yych;
				$yych = $YYBUFFER[++$YYCURSOR];
			}
			$value = strtolower($value);
			if ($value === 'order') {
				while ($this->_SCAN_WS[$YYBUFFER[$YYCURSOR]]) {
					$YYCURSOR++;
				}
				$yych  = $YYBUFFER[$YYCURSOR];
				if ($this->_SCAN_AZ[$yych]) {
					$value = $yych;
					$yych  = $YYBUFFER[++$YYCURSOR];
					while ($this->_SCAN_AZ[$yych]) {
						$value .= $yych;
						$yych = $YYBUFFER[++$YYCURSOR];
					}
					$value = strtolower($value);
					if ($value === 'by') {
						$YYBUFFER = substr($YYBUFFER, $YYCURSOR;
						$result = $this->parse_or_expr($YYBUFFER);
						$YYCURSOR = 0;
						$value = '';
					} else {
						$this->error = "syntax error near: $YYBUFFER";
						return false;
					}
				}				
			}
		}

*/		

		if (preg_match('|^[[:space:]]*join[[:space:]]*target[[:space:]]*on[[:space:]]*|i', $query, $regs)) {
			$this->join_target_properties = Array();
			$query = substr($query, strlen($regs[0]));
			$this->parse_join_target_properties($query);
		}

		$matching = preg_match('|^[[:space:]]*order[[:space:]]*by[[:space:]]+|i', $query, $regs);
		if ( $matching || $no_selection ) {
			$query=substr($query, strlen($regs[0]));
			$node["id"]="orderby";
			$node["right"]=$this->parse_orderby($query);
			$node["left"]=$result;
			$result=$node;
		}
		if (preg_match('|^[[:space:]]*limit[[:space:]]+([0-9]+)[[:space:]]*([,][[:space:]]*([0-9]+))?|i', $query, $regs)) {
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
	protected abstract function priv_sql_compile($node) ;

	public function compile($path, $query, $limit=100, $offset=0, $layers = array()) {
		debug("sql_compiler::compile ($path, $query, $limit, $offset, $layer)", "store");
		$this->error="";
		$this->path = $path;

		$this->limit=$limit;
		$this->offset=$offset;
		$this->layers=$layers;

		$tree=$this->parse_query($query);
		if (!$this->error && trim($query)) {
			$this->error="unkown operator near '$query'";
			$result="";
		} else {
			if ($tree) {
				$compiled_query=$this->priv_sql_compile($tree);
			}
		}
		return $compiled_query;
	}


  }
?>