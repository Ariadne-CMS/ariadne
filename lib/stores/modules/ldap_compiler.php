<?php
  include_once($store->get_config("code")."stores/modules/sql_compiler.php");

  class ldap_compiler extends sql_compiler {
	function ldap_compiler(&$store, $mappings) {
		debug("ldap_compiler($tbl_prefix)", "store");
		$this->tbl_prefix=$tbl_prefix;
		$this->store=$store;
		$this->buildlist=array();
		$this->mappings=$mappings;
	}

	function compile_tree(&$node, $arguments=null) {

		if ($arguments) {
			extract($arguments);
		}

		switch ((string)$node["id"]) {
			case 'implements':
				$result="prop_object.AR_implements";
			break;
			case 'property':
				$table=$node["table"];
				$field=$node["field"];
				$result="$table.$field";
			break;
			case 'ident':
				$table=$node["table"];
				$field=$node["field"];
				$result="prop_object.AR_$field";
			break;
			case 'custom':
				$table="prop_custom";
				$field=$node["field"];
				$result="prop_my.AR_$field";
			break;
			case 'string':
				// LDAP filters don't have quotes around strings
				$result=$node["value"];
				$result=substr($result, 1, -1);
			break;
			case 'float':
			case 'int':
				$result=$node["value"];
			break;
			case 'and':
				if (preg_match("/^&[0-9]+$/", $this->buildlist[sizeof($this->buildlist)-1]) &&
				    !$this->groupingflag) {
					$this->buildlist[sizeof($this->buildlist)-1]="&".
				          (substr($this->buildlist[sizeof($this->buildlist)-1], 1)+1);
				} else {
					array_push($this->buildlist, '&2');
					$this->groupingflag=false;
				}
				$this->compile_tree($node["left"]);
				$this->compile_tree($node["right"]);
			break;
			case 'or':
				if (preg_match("/^\|[0-9]+/$", $this->buildlist[sizeof($this->buildlist)-1]) &&
				    !$this->groupingflag) {
					$this->buildlist[sizeof($this->buildlist)-1]="|".
				          (substr($this->buildlist[sizeof($this->buildlist)-1], 1)+1);
				} else {
					array_push($this->buildlist, '|2');
					$this->groupingflag=false;
				}
				$this->compile_tree($node["left"]);
				$this->compile_tree($node["right"]);
			break;
			case 'cmp':
				$not=false;
				$joker=false;
				$operator=$node["operator"];
				switch ($operator) {
					case '!~':
					case '!~~':
						$not=true;
 					case '~=':
					case '=~':
					case '=~~':
						$joker=true;
						$ecompare = '=';
						break;
					case '!=':
						$not=true;
						// fall through
					case '=':
					case '==':
						$ecompare = '=';
						break;
					case '>':
						$not=true;
						// fall through
					case '<=':
						$ecompare = '<=';
						break;
					case '<':
						$not=true;
						// fall through
					case '>=':
						$ecompare = '>=';
						break;
				}
				$left=$this->compile_tree($node["left"]);
				$right=$this->compile_tree($node["right"]);

				// Quote the characters that have a special meaning in LDAP filters
				// as per RFC 2254
				$ldapspecial=array("\\", "*", "(", ")", "\x00");
				$ldapreplace=array("\\5c", "\\2a", "\\28", "\\29", "\\00");
				$evalue=str_replace($ldapspecial, $ldapreplace, $right);

				if ($joker) {
					// Replace '%' with '*' for the LDAP filter
					$evalue=str_replace('%', '*', $evalue);
				}

				if (isset($this->mappings["$left$operator$right"])) {
					// Complete translation (property name and value)
					$result=$this->mappings["$left$operator$right"];
				} elseif (isset($this->mappings["$left"])) {
					// Only the property name should be translated
					$result=$this->mappings["$left"].$ecompare.$evalue;
				} elseif (substr($left, 0, 13)=="prop_ldap.AR_") {
					// Literal ldap attribute name given
					$result=substr($left, 13).$ecompare.$evalue;
				} else {
					// Hmm, unknown property, maybe we should throw
					// an error here? (TODO)
					$result="$left$ecompare$evalue";
				}

				// Ignore the parent property, is it makes
				// no sense here (objectclass=* matches
				// every LDAP object)
				if ($left=='prop_object.AR_parent') {
					$result='objectclass=*';
				}

				if ($not) {
					$result="!($result)";
				}

				array_push($this->buildlist, $result);
			break;
			case 'group':
				$this->groupingflag=true;
				$left=$this->compile_tree($node["left"]);
			break;

			case 'orderby':
				$result=$this->compile_tree($node["left"]);
				$this->orderby_s=$this->compile_tree($node["right"]);
			break;

			case 'orderbyfield':
				$this->in_orderby = true;
				$left=$this->compile_tree($node["left"]);
				$right=$this->compile_tree($node["right"]);
				if (isset($this->mappings["$right"])) {
					// Property name should be translated
					$right=$this->mappings["$right"];
				} elseif (substr($right, 0, 13)=="prop_ldap.AR_") {
					// Literal ldap attribute name given
					$right=substr($right, 13);
				} else {
					// Hmm, unknown property, maybe we should throw
					// an error here? (TODO)
				}
				$this->orderbyfield=$right;
			break;

			case 'limit':
				$this->compile_tree($node["left"]);
				// Ignore limit and offset as LDAP does
				// not support it as complete as we need it
			break;
		}

		return $result;
	}

	// ldap specific compiler function
	function priv_sql_compile($tree) {
		$this->compile_tree($tree);

		//print implode(" - ", $this->buildlist)."<br>\n";

		$query="";
		$groupstack=array();
		foreach ($this->buildlist as $item) {
			if (sizeof($groupstack)>0) {
				$groupstack[sizeof($groupstack)-1]--;
			}
			if (preg_match("/^[&\|][0-9]+$/", $item)) {
				array_push($groupstack, substr($item, 1));
				$query.="(".substr($item, 0, 1);
			} else {
				$query.="(".$item.")";
			}
			if (sizeof($groupstack)>0 &&
			    $groupstack[sizeof($groupstack)-1]==0) {
				array_pop($groupstack);
				$query.=")";
			}
		}

		if (sizeof($groupstack)) {
			$query.=")";
		}

		return $query;
	}

  }

?>