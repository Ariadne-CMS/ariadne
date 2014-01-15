<?php
	$userConfig = $this->loadUserConfig();
	$authconfig = $userConfig['authentication'];

	if( !function_exists("arGetGrantType") ) {
		function arGetGrantType($value) {
			if (($value & ARMASKLOCAL) && ($value & ARMASKCHILDREN)) { 
				$result=""; 
			} else if ($value & ARMASKLOCAL) {
				$result="=";
			} else {
				$result=">";
			}
			return $result;
		}
	}
	
	if( !function_exists("array_compare") ) {
		function array_compare(&$ar1, &$ar2) {
			if (count($ar1) != count($ar2)) {
				return false;
			} else {
				foreach ($ar1 as $key => $value) {
					if (is_array($value) && is_array($ar2[$key])) {
						return array_compare($ar1[$key], $ar2[$key]);
					} else 
					if ($value !== $ar2[$key]) {
						return false;
					}
					return true;
				}
			}
		} 
	}
	
	if( !function_exists("getClass") ) {
		function getClass($grey=false) {
			global $ARCurrent;
			if ($ARCurrent->oddline=($ARCurrent->oddline+1)%2) {
				$class='odd';
			} else {
				$class='even';
			}
			if ($grey) {
				$class .= '-grey';
			}
			return $class;
		}
	}
	
	if( !function_exists("getPathByType") ) {
		function getPathByType($type, $id) {
			global $AR;
			switch ($type) {
				case 'pgroup':
					foreach ($authconfig['groupdirs'] as $groupdir) {
						$path  = current($AR->user->find($groupdir, 'login.value=\''.$id.'\'', 'system.get.path.phtml'));
						if ($path) {
							break;
						}
					}
					break;
				case 'puser':
					foreach ($authconfig['userdirs'] as $userdir) {
						$path  = current($AR->user->find($userdir, 'login.value=\''.$id.'\'', 'system.get.path.phtml'));
						if ($path) {
							break;
						}
					}
					break;
			}
			return $path;
		}
	}
	
	if( !function_exists("removeSimilarGrants") ) {
		function removeSimilarGrants($grants) {
			// first check for similar grants (same modifiers)
			$grants_check = $grants;
			foreach ($grants as $grant => $grant_ar) {
				// loop $grants for each entry in $grants_check (which is a copy)
				array_shift($grants_check);
				if (is_array($grant_ar)) {
					foreach ($grants_check as $grant_c => $grant_ar_c) {
						if (is_array($grant_ar_c) && array_compare($grant_ar, $grant_ar_c)) {
							unset($grants[$grant_c]);
						}
					}
				}
			}
			reset($grants);
			ksort($grants);
			return $grants;
		}
	}
	
	if( !function_exists("getGrantString") ) {
		function getGrantString($id, $type, $grants, $grey) {
			if ($grants && is_array($grants)) {
				$grants = removeSimilarGrants($grants);
				$grant_display = '';
				$grant_string = '';

				foreach ($grants as $grant => $modifiers) {
					if (!is_array($modifiers)) {
						$grant_type=arGetGrantType($modifiers);
						$grant_display .= htmlspecialchars($grant_type); // echo
					} else {
						$grant_type='';
					} 

					if (!$grey) {
						$grant_display .= "<a href=\"javascript:selectGrant('$type', '$id', '$grant');\">";
					} else {
						$grant_display .= "<span class='grey'>";
					}

					$grant_display .= $grant;
					if (is_array($grants_eq[$grant])) {
						foreach ($grants_eq[$grant] as $g_add) {
							$grant .= ", ".$grant_type."$g_add";
						}
						$grant_display .= "[$grant]";
						$grant_string .= "[$grant]";
					} else {
						$grant_string .= "$grant_type$grant ";
					}
					if (is_array($modifiers)) {
						$grant_string .= "( ";
						$grant_display .= "( ";
						foreach ($modifiers as $modifier => $value) {
							$grant_type=arGetGrantType($value);
							$grant_display .= htmlspecialchars($grant_type);
							$grant_display .= $modifier." "; 
							$grant_string .= $grant_type.$modifier." ";
						}
						$grant_display .= ") ";
						$grant_string .= ") ";
					}
					if (!$grey) {
						$grant_display .= "</a>";
					} else {
						$grant_display .= "</span>";
					}
				}
				$grant_display .= " ";
				$grant_string .= " ";
			}
			return array($grant_string, $grant_display);
		}
	}
	
	if( !function_exists("DisplayGrants") ) {
		function DisplayGrants(&$grantslist, $type, $grey=false) {
			global $AR, $ARCurrent;
			if ($grantslist[$type] && is_array($grantslist[$type])) {
				ksort($grantslist[$type]);
				while (list($id, $grants)=each($grantslist[$type])) { // path was login en is weer login
					$grant_string = "";
					if (!$ARCurrent->donelist[$type][$id]) {
						$ARCurrent->donelist[$type][$id]=true;
						$row_id = "grants-$type-$id";
						$row_class = getClass($grey);
						$path = getPathByType($type, $id);
						$grant_strings = getGrantString($id, $type, $grants, $grey);
						$grant_string = $grant_strings[0];
						$grant_html = $grant_strings[1]; 
						$icon = $AR->dir->images . "icons/small/" . $type . ".png";

						?>
						<script type="text/javascript">
							id2path['<?php echo $type; ?>']['<?php echo $id; ?>'] = '<?php echo $path; ?>';
							grant_strings['<?php echo $type; ?>']['<?php echo $id; ?>']='<?php echo $grant_string; ?>';
						</script>
						<tr class='<?php echo $row_class; ?>' id="<?php echo $row_id; ?>">
							<td>
								<img alt="<?php echo $type; ?>" src="<?php echo $icon; ?>">
							</td>
							<td>
								<a href="javascript:loadUser('<?php echo $type; ?>', '<?php echo $id; ?>');"><?php echo $id; ?></a>
							</td>
							<td>
								<?php echo $grant_html; ?>
							</td>
						</tr>
						<?php
					}
				}
			}
		}
	}
?>
<script type="text/javascript">
  var ARMASKLOCAL = <?php echo ARMASKLOCAL; ?>;
  var ARMASKCHILDREN = <?php echo ARMASKCHILDREN; ?>;
  var ARMASKGLOBAL = <?php echo ARMASKGLOBAL; ?>;
  var ARGRANTOLD = <?php echo ARGRANTOLD; ?>;
  var ARGRANTLOCAL = <?php echo ARGRANTLOCAL; ?>;
  var ARGRANTCHILDREN = <?php echo ARGRANTCHILDREN; ?>;
  var ARGRANTGLOBAL = <?php echo ARGRANTGLOBAL; ?>;

  var grants = new Array();
  grants['puser'] = new Array();
  grants['pgroup'] = new Array();

  function editUserGrants(type, id) {
	var userGrants = new Array;
	// copy user grants
	userGrants.concat(grants[type][id]);
	selectUser(type, id, grants[type][id]);
  }

  function editGrant(type, id, grant) {
	var userGrants = new Array;
	// copy user grants
	userGrants.concat(grants[type][id]);
	selectGrant(type, id, grant, grants[type][id]);
  }

  id2path = new Array();
  id2path['pgroup'] = new Array();
  id2path['puser'] = new Array();

  grant_strings = new Array();
  grant_strings['pgroup'] = new Array();
  grant_strings['puser'] = new Array();
  
<?php
	if (is_array($data->config->grants)) {
		foreach ($data->config->grants as $type => $grantslist) {
			while (list($id, $grants)=@each($grantslist)) {
				switch ($type) {
					case 'pgroup':
						foreach ($authconfig['groupdirs'] as $groupdir) {
							$path  = current($this->find($groupdir, 'login.value=\''.$id.'\'', 'system.get.path.phtml'));
							if ($path) {
								break;
							}
						}
					break;
					case 'puser':
						foreach ($authconfig['userdirs'] as $userdir) {
							$path  = current($this->find($userdir, 'login.value=\''.$id.'\'', 'system.get.path.phtml'));
							if ($path) {
								break;
							}
						}
					break;
				}
				echo "  id2path['$type']['$id'] = '$path'; \n";
				echo "  grants['$type']['$id'] = new Array();\n";
				if ($grants && is_array($grants)) {
					ksort($grants);
					while (list($grant, $modifiers)=each($grants)) {
						echo "  grants['$type']['$id']['$grant'] = new Array();\n";
						if (!is_array($modifiers)) {
							echo "  grants['$type']['$id']['$grant']['*'] = $modifiers; \n";
						} else
						if (is_array($modifiers)) {
							while (list($modifier, $value)=each($modifiers)) {
								echo "  grants['$type']['$id']['$grant']['$modifier'] = $value;\n";
							}
						}
					}
				}
			}
		}
	}
?>
</script>

	<table cellspacing="0">
		<thead>
		<tr>
			<td>&nbsp;</td>
			<td><?php echo $ARnls["name"]; ?></td>
			<td><?php echo $ARnls["grants"]; ?></td>
		</tr>
		</thead>
		<tbody>
		<?php
			if ($data->config->grants && is_array($data->config->grants)) {
				DisplayGrants($data->config->grants,"pgroup");
				DisplayGrants($data->config->grants,"puser");
			}

			// get a list of all previous defined grants
			// skip the current object
			if ($this->parent!='..') {
				$grants=$this->parents($this->parent, 'system.get.grants.phtml','','/');
			}
			// then display them beginning with the current defined grants
			if (is_array($grants)) {
				while (list($key, $localgrants)=each($grants)) {
					$list=Array('pgroup','puser');
					while (list($key, $type)=each($list)) {
						if (is_array($localgrants[$type])) {
							while (list($gkey, $gval)=each($localgrants[$type])) {
								if ($type=='pgroup' && is_array($prevgrants[$type][$gkey]) && is_array($gval)) {
									$prevgrants[$type][$gkey]=array_merge($gval, $prevgrants[$type][$gkey]);
								} else if ($gval && !is_array($gval)) {
									$prevgrants[$type][$gkey] = $gval;
								} else if ($gval) {
									$prevgrants[$type][$gkey] = $gval;
								}
							}
						}
					}
				}
				DisplayGrants($prevgrants, "pgroup", true);
				DisplayGrants($prevgrants, "puser", true);
			} 
		?>
		</tbody>
	</table>