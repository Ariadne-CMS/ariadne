<?php
	include_once("getvars.php");
	include_once("nls." . $language . ".php");
	include("step_header.php");
	$stepnum = substr($step, 4, strlen($step));
	$next = $stepnum+1;
	$previous = $stepnum-1;

	$nextstep = "step$next";
	$previousstep = "step$previous";
	$oddeven = "even";

	/* checks to do:
		admin user
			make sure the password for the admin user matches
		config file
			check if config can be written
		database
			connect
			see if database exists, if so cancel installation with an error. in a later version, give the option to overwrite the database, upgrade it, move etc.
			check create database grants
			check create table, insert, delete, update etc grants on the database.

		recheck all the pre-install checks just to be sure.
		if any of the checks fail, show what the problem is and suggest a solution.

		if no checks have failed, we should be ok to do the installation;
			write config file
			create database
			import base ariadne data
			import extra modules/libs as specified
			set new admin account

	*/

		// fixme: check if it is there first.
		@include('../ariadne.inc');
		if ($ariadne) {
			$ar_dir_install = $ariadne;
			$ar_dir_install = preg_replace("|.lib$|", '', $ar_dir_install);
		}

		$os_names = array(
			// FIXME: Add more types;
			"Linux" => "UNIX",
			"Windows NT" => "WIN32"
		);

		if ($os_names[php_uname('s')]) {
			$ar_os = $os_names[php_uname('s')];
		} else {
			$uname = php_uname('s');
			if (preg_match("/indows/", $uname)) {
				$ar_os = "WIN32";
			} else {
				$ar_os = "UNIX";
			}
		}

		$ar_dir_www = $_SERVER['PHP_SELF'];

		$docroot = $_SERVER['DOCUMENT_ROOT'];
		$ar_dir_www = preg_replace("|/install/index.php$|", '', $ar_dir_www);

		if ($enable_workspaces) {
			$database = $database . "_workspaces";
		}

		// declare default object,
		if (!class_exists('baseObject',false)) {
			class baseObject { }           // do not change
		}

		// Generate the config file.
		ob_start();
			include("conf/global.conf");
			echo "\$AR->OS = '$ar_os';\n";
			include("conf/errors.conf");
			echo "\$AR->dir->www = '$ar_dir_www';\n";
			echo "\$AR->dir->install = '$ar_dir_install';\n";
			echo "\$AR->DB->dbms = '$database';\n";
			echo "\$AR->DB->host = '$database_host';\n";
			echo "\$AR->DB->database = '$database_name';\n";
			echo "\$AR->DB->user = '$database_user';\n";
			echo "\$AR->DB->password = '$database_pass';\n";

			include("conf/salt.conf");
			include("conf/session.conf");
			include("conf/loader.conf");
			include("conf/im.conf");
			include("conf/svn.conf");

			include("conf/tidy.conf");
			include("conf/webkit2png.conf");
			include("conf/grep.conf");
		$configfile = ob_get_contents();
		ob_end_clean();

		eval($configfile);

		function write_config($location, $configfile) {
			include("../ariadne.inc");
			$fh = fopen($location, "w");
			fwrite($fh, "<?php\n");
			fwrite($fh, $configfile);
			fwrite($fh, "\n");
			fclose($fh);
		}

		$checks = array(
			"check_ariadne_inc_read" => array("check_file", $ariadne),
			"check_admin_password" => array("check_admin_password", array($admin_pass, $admin_pass_repeat)),
			"check_configs" => array("check_file", $ariadne . "/configs"),
//			"check_ariadne_phtml" => array("check_file", $ariadne . "/configs/ariadne.phtml"),
			"check_store_phtml" => array("check_file", $ariadne."/configs/store.phtml"),
			"check_axstore_phtml" => array("check_file", $ariadne."/configs/axstore.phtml"),
			"check_includes" => array("check_file", $ariadne."/includes/"),
			"check_loader_web_php" => array("check_file", $ariadne."/includes/loader.web.php"),
			"check_stores" => array("check_file", $ariadne."/stores/"),
			"check_selected_db_store" => array("check_file", $ariadne."/stores/".$database."store.phtml"),
			"check_selected_db_store_install" => array("check_file", $ariadne."/stores/".$database."store_install.phtml"),
			"check_nls" => array("check_file", $ariadne."/nls/" ),
			"check_default_nls" => array("check_file", $ariadne."/nls/".$AR->nls->default),
//			"check_ariadne_phtml_write" => "check_ariadne_phtml_write",
			"check_connect_db" => array("check_connect_db",$AR->DB),
			"check_select_db" => array("check_select_db",$AR->DB),
			"check_db_grants" => array("check_db_grants",$AR->DB),
			"check_db_is_empty" => array("check_db_is_empty",$AR->DB),
			"check_db_charset" => array("check_db_charset",$AR->DB),
			"check_files" => array("check_file", $ar_dir_install . "/files/"),
			"check_files_write" => "check_files_write",
			"check_base_ax" => "check_base_ax",
		);

		if ($downloaded_config) {
			$checks["check_ariadne_phtml"] = array("check_file", $ariadne . "/configs/ariadne.phtml");
		}
		if ($install_demo) {
			$checks["check_demo_ax"] = "check_demo_ax";
		}
		if ($install_docs) {
			$checks["check_docs_ax"] = "check_docs_ax";
		}
		if ($install_libs) {
			$checks["check_libs_ax"] = "check_libs_ax";
		}

		$install_steps = array();
		if (!$downloaded_config) {
			$install_steps[] = "write_config_file";
		}
		$install_steps[] = "init_database";
		$install_steps[] = "install_base_package";
		if ($install_demo) {
			$install_steps[] = "install_demo";
		}
		if ($install_libs) {
			$install_steps[] = "install_libs";
		}
		if ($install_docs) {
			$install_steps[] = "install_docs";
		}

		function set_progress($id, $progress) {
			if ($id) {
				?>
				<script type="text/javascript">
					document.getElementById("<?php echo $id; ?>_step").className = "step current";
					document.getElementById("<?php echo $id; ?>_progress").style.width = '<?php echo $progress; ?>%';
				</script>
				<?php
			}
			flush();
		}

		function progress($current, $total) {
			global $target_id;
			if ($total > 0) {
				$progress = (int)(100*($current)/$total);
				set_progress($target_id, $progress);
			}
		}

		function display() {
		}
?>
			<div id="sectiondata">
				<?php include("sections.php"); ?>
				<div id="tabs">
				</div>
				<div id="tabsdata">
					<h1><?php echo $ARnls['install:installing']; ?></h1>
					<?php
						$checkresult = true;
						$checkresults = array();
						foreach($checks as $check_name => $check) {
							if (is_array($check)) {
								$function = $check[0];
								$args = $check[1];
							} else {
								$function = $check;
								$args = array();
							}
							$checkresults[$check_name] = call_user_func($function, $args);
							$checkresult = $checkresult && $checkresults[$check_name];
						}

						if ($checkresult) {
							if (check_ariadne_phtml_write() || $downloaded_config) {
								?>
								<h2><?php echo $ARnls['install:running']; ?></h2>
								<?php
									foreach ($install_steps as $install_step) {
										// show the steps;
									?>
									<div class="step" id="<?php echo $install_step; ?>_step">
										<div class="progress" id="<?php echo $install_step; ?>_progress"></div>
										<div class="step_desc"><?php echo $ARnls['install:' . $install_step]; ?></div>
									</div>
								<?php		flush();
									}
								if (!$downloaded_config) {
									set_progress("write_config_file", 0);
									write_config($ariadne . "/configs/ariadne.phtml", $configfile);		// Write the config file
									set_progress("write_config_file", 100);
								}
								set_progress("init_database", 0);
								ob_start();
								include("init_database.php"); 		// Install Ariadne tables
								ob_end_clean();
								set_progress("init_database", 100);
								set_progress("install_base_package", 0);
								$target_id = "install_base_package";
								include("install_base_package.php"); 	// Install Ariadne base package;
								$target_id = '';
								set_progress("install_base_package", 100);
								if ($install_demo) {
									$target_id = "install_demo";
									set_progress("install_demo", 0);
									include("install_demo_package.php");
									set_progress("install_demo", 100);
								}
								if ($install_libs) {
									set_progress("install_libs", 0);
									set_progress("install_libs", 100);
								}
								if ($install_docs) {
									set_progress("install_docs", 0);
									set_progress("install_docs", 100);
								}
								if (check_admin_password(array($admin_pass, $admin_pass_repeat))) {
									$new_admin_password = $admin_pass;
									include("set_admin_password.php");
								}

								/*
									FIXME:
									5. Remove write grants on ariadne.phtml
									6. Remove read grants on the installation directory

									Done!
								*/
							?>
					<h2><?php echo $ARnls['install:success']; ?></h2>
					<pre>
						- remove write grants on ariadne.phtml
						- schedule the cleanup script
						- remove the installation directory
					</pre>
							<?php

							} else {
								write_config($ar_dir_install . "/files/temp/ariadne.phtml", $configfile);
								?>
								<h2><?php echo $ARnls['install:download_config']; ?></h2>
								<p><?php echo $ARnls['install:cant_write_config']; ?></p>
								<p><?php echo $ARnls['install:to_continue']; ?> <a href="download_config.php" target="_blank"><?php echo $ARnls['install:download']; ?></a> <?php echo $ARnls['file_should_be']; ?>
								<p><?php echo $ARnls['continue_when_done']; ?></p>
								<input type="submit" class="button" name="downloaded_config" value="<?php echo $ARnls['install:continue_install']; ?>">
								<?php
								// show button to install after configfile has been placed.
							}
						} else {
							// echo the errors found in the check and add suggestions to fix the problem.
							?>
							<h2><?php echo $ARnls['install:problem_encountered']; ?></h2>
							<p><?php echo $ARnls['install:please_review_problems']; ?></p>
							<table class="checks">
								<colgroup>
									<col class="col1">
									<col class="col2">
								</colgroup>
								<?php
								foreach ($checkresults as $key => $check_value) {
									$oddeven = $oddeven == 'odd' ? 'even' : 'odd';
									if ($check_value == true) {
										$checkresult = "<span class='passed'>".$ARnls['install:check_ok']."</span>";
									} else {
										$checkresult = "<span class='failed'>".$ARnls['install:check_failed']."</span>";
									}
									?>
									<tr class="<?php echo $oddeven; ?>"><td><?php echo $ARnls['install:' . $key]; ?></td><td><?php echo $checkresult; ?></td></tr>
								<?php	}?>
							</table>
						<?php
						}
					?>
				</div>
			</div>
			<div class="buttons">
				<div class="right">
					<label class="button" for="previous"><?php echo $ARnls['install:previous']; ?></label>
					<input class="hidden" id="previous" type="submit" name="step" value="<?php echo $previousstep?>">
					<label class="button" for="next"><?php echo $ARnls['install:next']; ?></label>
					<input class="hidden" id="next" type="submit" name="step" value="<?php echo $nextstep; ?>">
				</div>
			</div>
<?php include("step_footer.php"); ?>
