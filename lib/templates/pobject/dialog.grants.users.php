<?php
	include_once("dialog.grants.logic.php");
	
	include_once($this->store->get_config("code")."modules/mod_yui.php");

	$userConfig = $this->loadUserConfig();
	$authconfig = $userConfig['authentication'];
	
	define('ARGRANTBYTYPE', 8);

	$selectedpath = $this->getdata("selectedpath");
	$selecteduser = $this->getdata("selecteduser");
	$moregrants = $this->getdata("moregrants");
	$textmode = $this->getdata("textmode");
	$stored_vars = $this->getdata("arStoreVars");
	$data = $this->getdata('data');

	if (!$selectedpath) {
		$selectedpath = $this->path;
	}

	$defaultGroupDir    = "/system/groups/";
	if (is_array($authconfig['groupdirs'])) {
		// find closest group directory
		$nMatch = 0;
		foreach ($authconfig['groupdirs'] as $groupDir) {
			$length = min(strlen($this->path), strlen($groupDir));
			for ($i = 0; $i < $length && $this->path[$i] === $groupDir[$i]; $i++);
			if ($i > $nMatch) {
				$nMatch = $i;
				$defaultGroupDir = $groupDir;
			}
		}
	}


	$default_grants = array(
		"read" => "Read",
		"add" => "Add",
		"edit" => "Edit",
		"layout" => "Layout",
		"config" => "Config",
		"delete" => "Delete",
	);

	$available_grants = $default_grants;

	$users = array();
	$selectedob = current($this->get($selectedpath, "system.get.phtml"));
	while (($selectedob->parent != '..')) {
		if($selectedob && $selectedob->data->config->grants) {
			foreach ($selectedob->data->config->grants as $type => $grant) {
				foreach ($grant as $id => $grants) {
					if ($type == 'pgroup') {
						foreach ($authconfig['groupdirs'] as $groupdir) {
							$path  = current($this->find($groupdir, 'login.value=\''.$id.'\'', 'system.get.path.phtml'));
							$name  = current($this->find($groupdir, 'login.value=\''.$id.'\'', 'system.get.name.phtml'));
							if ($path) {
								break;
							}
						}
					} else {
						// type is puser;
						foreach ($authconfig['userdirs'] as $userdir) {
							$path  = current($this->find($userdir, 'login.value=\''.$id.'\'', 'system.get.path.phtml'));
							$name  = current($this->find($userdir, 'login.value=\''.$id.'\'', 'system.get.name.phtml'));
							if ($path) {
								break;
							}
						}
					}

					$grantsstring = grantsArrayToString($grants);
					$grants_by_type = array();
					foreach ($grants as $grantname => $grantvalue) {
						if (!isset($available_grants[$grantname])) {
							$available_grants[$grantname] = yui::labelspan($grantname, 8);
						}
						if (is_array($grantvalue)) {
							$grants_by_type[$grantname] = $grantvalue;
							$grants[$grantname] = ARGRANTBYTYPE;
						}
						if (!is_array($users[$path])) {
							$users[$path] = array(
								"name" => $name,
								"type" => $type,
								"grants" => array(
									'array' => $grants,
									'bytype' => $grants_by_type,
									"grantsstring" => $grantsstring
								)
							);
							if ($selectedob->path != $selectedpath) {
								$users[$path]["grants_inherited"] = 1;
							}
						}
					}
				}
			}
		}
		$selectedob = current($this->get($selectedob->parent, "system.get.phtml"));
		$maxloop--;
	}

	$extrausers = $this->getdata("extrausers");
	if (!is_array($extrausers)) {
		$extrausers = array();
	}
	if ($users[$selecteduser]['grants_inherited']) {
		$extrausers[] = $selecteduser;
	}

	
	foreach ($extrausers as $key => $extrauser) {
		if ($users[$extrauser]) {
			if ($users[$extrauser]['grants_inherited']) {
				unset($users[$extrauser]);
			} else {
				continue;
			}
		}
		if (!$this->exists($extrauser)) {
			unset($extrausers[$key]);
			continue;
		} else {
			$extra_ob = current($this->get($extrauser, 'system.get.phtml'));

			// FIXME: Add check on type for puser/pgroup and subtypes of those.
			if (
				strpos($extra_ob->type, "puser") === 0 ||
				strpos($extra_ob->type, "pgroup") === 0
			) {
				$users[$extra_ob->path] = array(
					"name" => $extra_ob->nlsdata->name,
					"type" => $extra_ob->type,
					"grants" => array(
						'array' => array(),
						'bytype' => '',
						'grantsstring' => ''
					)
				);
			} else {
				$error = "Object $extrauser is not a user or group";
				unset($extrausers[$key]);
			}
		}
	}

	$useradd = $this->getvar("useradd");
	if ($useradd) {
		if ($this->exists($extrauser)) {
			if (!$error) {
				$selecteduser = $extrauser; // Select the new user.
			}
		} else {
			$error = "User $extrauser not found.";
		}
	} else {
		$error = '';
	}

	$add_bytype = $this->getvar("add_bytype");
	if ($add_bytype) {
		$typename = $this->getvar("typename");
		$data[$selectedpath][$selecteduser]['grants']['bytype'][$moregrants][$typename] = ARGRANTGLOBAL;
	}

	function arrayMergeCorrect($left, $right) {
		if (is_null($right)) {
			return $left;
		}
		if (is_array($right)) {
			foreach ($right as $key => $value) {
				if (!is_numeric($key)) {
					$left[$key] = arrayMergeCorrect($left[$key], $value);
				} else {
					$left[] = arrayMergeCorrect($left[$key], $value);
				}
			}
			return $left;
		} else {
			return $right;
		}
	}


	$typetree = $this->call('typetree.ini');
	$typenames = $this->getvar("arTypeNames");

/*	// FIXME: Types met grants uit de grantsstring vissen.
	$types = array(
		"particle" => "Article",
		"pbookmark" => "Bookmark",
		"ppage" => "Page",
		"psite" => "Site"
	);
*/

	$modifiers = array(
		"Default" => ARGRANTGLOBAL,
		"Current only" => ARGRANTLOCAL,
		"Children only" => ARGRANTCHILDREN,
		"By type" => ARGRANTBYTYPE,
		"Unset grant" => 0
	);

	$modifiers = array(
		"*" => ARGRANTGLOBAL,
		"=" => ARGRANTLOCAL,
		">" => ARGRANTCHILDREN,
		"T" => ARGRANTBYTYPE,
		"X" => 0
	);

	$ob_id = str_replace("/", ":", $selectedpath);
?>
<div class="items">
	<h2><?php echo $ARnls['ariadne:grants:users_with_grants']; echo yui::labelspan($selectedpath, 20); ?></h2>
	<input type="hidden" name="selecteduser" value="<?php echo htmlspecialchars($selecteduser); ?>">
	<?php if ($error) { ?>
		<div class="error"><?php echo $error; ?></div>
	<?php } ?>
	<?php	foreach ($users as $path => $info) { 
			$user_id = str_replace("/", ":", $path);	
			$formdata = $data[$selectedpath][$path];
			$stored_formdata = $stored_vars['data'][$selectedpath][$path];

			// Merge info fromdata form with $info
			$info['grants'] = arrayMergeCorrect($info['grants'], $stored_formdata['grants']);
			$info['grants'] = arrayMergeCorrect($info['grants'], $formdata['grants']);
//			$info['grants'] = array_merge($info['grants'], $stored_formdata['grants'], $formdata['grants']);
//			echo "<pre>";
//			print_r($info);
//			echo "</pre>";
	?>
		<div class="item<?php if($path == $selecteduser) { echo " selected";} if ($info['grants_inherited']) { echo " inherited";} ?>">
			<div class="info">
				<label class="block" for="selectuser_<?php echo $user_id; ?>">
					<img src="<?php echo $this->call('system.get.icon.php', array('type' => $info['type'], 'size' => 'medium'));?>" alt="<?php echo $info['type']; ?>">
					<span class="name"><?php echo $info['name']; ?></span><br>
					<span class="grants_string"><?php echo $info['grants']['grantsstring']; ?></span>
				</label>
				<input type="submit" name="selecteduser" class="hidden" value="<?php echo $path; ?>" id="selectuser_<?php echo $user_id; ?>">
			</div>
			<?php 	if (!$info['grants_inherited']) { ?>
				<?php	if($textmode) {	?>
					<label class="textmode block" for="textmode"></label>
					<input class="hidden" type="submit" name="textmode" value="0" id="textmode">
					<div class="grants_textmode">
						<h2>Advanced grants</h2>
						<textarea class="grantstext" name="data[<?php echo $selectedpath;?>][<?php echo $path; ?>][grants][grantsstring]" rows=4 cols=30><?php echo $info['grants']['grantsstring']; ?></textarea>
					</div>
				<?php	} else {	?>
					<label class="textmode block" for="textmode"></label>
					<input class="hidden" type="submit" name="textmode" value="1" id="textmode">
					<div class="grants">
						<?php	foreach ($available_grants as $grant => $grant_name) { 
								if ($info['grants']['array'][$grant]) {
									$checked = "checked = 'checked' ";
									$value = $info['grants']['array'][$grant];
								} else {
									$checked = '';
									$value = ARGRANTGLOBAL;
								}
								if ($grant == $moregrants) {
									$checked .= "disabled";
								}
								if ($info['grants']['array'][$grant] == 0 || $info['grants']['array'][$grant] == 6) {
									// normal grants;
									$labelclass="normal";
								} else {
									$labelclass="specific";
								}
									

								if (is_array($info['grants']['bytype'])) {
									foreach ($info['grants']['bytype'] as $bytype_grant => $bytype_types) {
										foreach ($bytype_types as $bytype_type => $bytype_value) {
											$dataname = "data[$selectedpath][$path][grants][bytype][$bytype_grant][$bytype_type]";
											?>
											<input type="hidden" name="<?php echo $dataname; ?>" value="<?php echo $bytype_value; ?>">
											<?php
										}
									}
								}
						?>
							<div class="field checkbox <?php echo $class; ?>">
								<input name="data[<?php echo $selectedpath; ?>][<?php echo $path; ?>][grants][array][<?php echo $grant; ?>]" type='hidden' value='0'>
								<input class="<?php echo $extraclass; ?>" name="data[<?php echo $selectedpath; ?>][<?php echo $path; ?>][grants][array][<?php echo $grant; ?>]" <?php echo $checked; ?> type='checkbox' id='<?php echo $grant; ?>' value='<?php echo $value;?>'>
								<label class="<?php echo $labelclass; ?>" for='<?php echo $grant; ?>'><?php echo $grant_name; ?></label>
								<label for="moregrants_<?php echo $grant; ?>" class="block more" title="More grants"></label>
								<?php if ($grant == $moregrants) { ?>
									<input type="submit" class="hidden" value="" name="moregrants" id="moregrants_<?php echo $grant; ?>">
								<?php } else { ?>
									<input type="submit" class="hidden" value="<?php echo $grant; ?>" name="moregrants" id="moregrants_<?php echo $grant; ?>">
								<?php } ?>
							</div>
						<?php	}	?>
						<div class="clear"></div>
						<?php 	if ($moregrants) {	?>
							<div class="moregrants">
								<h2>More grants: <?php echo $moregrants; ?></h2>
								<div class="modifier">
									Grant modifier
									<?php 
										$name="data[$selectedpath][$path][grants][array][$moregrants]";
									?>
										<input type="hidden" value="<?php echo $info['grants']['array'][$moregrants]; ?>" name="<?php echo $name;?>">
									<?php
										foreach ($modifiers as $modname => $modvalue) { 
											$selected = '';
											//echo "[" . $info['grants']['array'][$moregrants] . " == " . $modvalue . "]";
											if ($info['grants']['array'][$moregrants] == $modvalue) {
												$selected = 'selected';
											}
											?>
											<label class="modifier <?php echo $selected;?>" for="mg_<?php echo $user_id . ":" . $modvalue; ?>"><?php echo $modname; ?></label>
											<input type='submit' class='hidden' value="<?php echo $modvalue;?>" id="mg_<?php echo $user_id . ":" . $modvalue; ?>" name="<?php echo $name;?>">
									<?php 	} ?>
								</div>

								<?php if ($info['grants']['array'][$moregrants] == ARGRANTBYTYPE) {	?>
									<h2>Type-specific grants</h2>
									<div class="addtype">
										<input type="hidden" value="0" name="add_bytype">
										<select name="typename">
										<?php	foreach ($typenames as $type => $name) {
												if (!isset($info['grants']['bytype'][$moregrants][$type])) {
										?>
											<option value="<?php echo $type; ?>"><?php echo $name; ?></option>
										<?php		}
											}	
										?>
										</select>&nbsp;<input class="button" type="submit" value="Add" name="add_bytype">
									</div>
									<div class="types">
										<?php	
											if (is_array($info['grants']['bytype']) && is_array($info['grants']['bytype'][$moregrants])) {
												foreach ($info['grants']['bytype'][$moregrants] as $type => $value) {
													$name = $typenames[$type];
										?>
											<div class="type">
												<div class="field checkbox">
													<!--input name="data[<?php echo $selectedpath; ?>][<?php echo $path; ?>][grants][bytype][<?php echo $moregrants; ?>][<?php echo $type; ?>]" value=<?php echo ARGRANTGLOBAL?> <?php echo $checked; ?>type='checkbox' id='<?php echo $moregrants . "_" . $type; ?>'-->
												</div>
												<img src="<?php echo $this->call('system.get.icon.php', array('type' => $info['type']));?>" alt="<?php echo $type; ?>">
												<span class="name"><?php echo $name; ?></span>

												<?php
													$dataname="data[$selectedpath][$path][grants][bytype][$moregrants][$type]";
												?>
													<input type="hidden" value="<?php echo $info['grants']['bytype'][$moregrants][$type]; ?>" name="<?php echo $dataname;?>">
												<?php foreach ($modifiers as $modname => $modvalue) {
													if ($modvalue == ARGRANTBYTYPE) {
														continue;
													}
													$selected = '';
													//echo "[" . $info['grants']['bytpe'][$moregrants][$type] . " == " . $modvalue . "]";
													if ($info['grants']['bytype'][$moregrants][$type] == $modvalue) {
														$selected = 'selected';
													}
													?>
													<label class="modifier <?php echo $selected;?>" for="mg_<?php echo $user_id . ":" . $moregrants . ":" . $type . ":" . $modvalue; ?>"><?php echo $modname; ?></label>
													<input type='submit' class='hidden' value="<?php echo $modvalue;?>" id="mg_<?php echo $user_id . ":" . $moregrants . ":" . $type . ":" . $modvalue; ?>" name="<?php echo $dataname;?>">
												<?php } ?>

											</div>
										<?php	
												}
											} 
										?>
									</div>
								<?php	} ?>
							</div>
						<?php	}?>
				
					</div>
				<?php	}	?>
			<?php	}	?>
		</div>
	<?php	}	?>
</div>
<div class="browse">
	<?php
		if (is_array($extrausers)) {
			foreach ($extrausers as $extrauser) {
	?>
			<input type='hidden' name="extrausers[]" value="<?php echo $extrauser; ?>">
	<?php
			}
		}
	?>
	<input type="text" id="extrauser" name="extrausers[]" value="<?php echo $defaultGroupDir; ?>">
	<input class="button" type="button" value="..." title="<?php echo $ARnls['browse']; ?>" onclick='callbacktarget="extrauser"; window.open("<?php echo $this->make_ariadne_url('/'); ?>" + document.getElementById("extrauser").value + "dialog.browse.php", "browse", "height=480,width=750"); return false;'>
	<input type="hidden" id="hidden_useradd" name="useradd" value=''>
	<input type="submit" class="button" name="useradd" value="<?php echo $ARnls['add']; ?>">
</div>
