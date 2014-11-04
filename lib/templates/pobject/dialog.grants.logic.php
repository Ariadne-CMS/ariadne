<?php
	$userConfig = $this->loadUserConfig();
	$authconfig = $userConfig['authentication'];

	if( !function_exists("grantsArrayToString") ) {
		function grantsArrayToString($grants) {
			$grantstring = "";
			if (is_array($grants)) {
				foreach ($grants as $grant => $granttype) {
					if (is_array($granttype)) {
						$grantstring .= " $grant ( ";
						reset($granttype);
						while (list($class, $modifierId)=each($granttype)) {
							if( $granttype > 0 ) {
								switch($modifierId) {
									case ARGRANTLOCAL:
										$modifier = "=";
									break;
									case ARGRANTCHILDREN:
										$modifier = ">";
									break;
									default:
										$modifier = "";
								}
								$grantstring .= " $modifier$class ";
							}
						}
						$grantstring .= " ) ";
					} elseif( $granttype > 0 ) {
						switch($granttype) {
							case ARGRANTLOCAL:
								$modifier = "=";
							break;
							case ARGRANTCHILDREN:
								$modifier = ">";
							break;
							default:
								$modifier = "";
						}
						$grantstring .= " $modifier$grant ";
					}
				}
			}
			return $grantstring;
		}
	}

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