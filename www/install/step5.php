<?php
	include_once("getvars.php");
	include_once("nls." . $language . ".php");
	include("step_header.php");
	$stepnum = substr($step, 4, strlen($step));
	$next = $stepnum+1;
	$previous = $stepnum-1;

	$nextstep = "step$next";
	$previousstep = "step$previous";
?>
			<div id="sectiondata">
				<?php include("sections.php"); ?>
				<div id="tabs">
				</div>
				<div id="tabsdata">
					<h1><?php echo $ARnls['install:ariadne_configuration']; ?></h1>
					<h2><?php echo $ARnls['install:ariadne_basic']; ?></h2>
					<table class="db_basic">
						<colgroup>
							<col class="col1">
							<col class="col2">
						</colgroup>
						<tr class="odd"><td>
							<label for="admin_pass"><?php echo $ARnls['install:admin_pass']; ?></label>
							<input class="text" id="admin_pass" type="password" name="admin_pass" value="<?php echo htmlspecialchars($admin_pass??''); ?>">
						</td><td>
							<?php echo $ARnls['install:admin_pass_help']; ?>
						</td></tr>
						<tr class="even"><td>
							<label for="admin_pass_repeat"><?php echo $ARnls['install:admin_pass_repeat']; ?></label>
							<input class="text" id="admin_pass_repeat" type="password" name="admin_pass_repeat" value="<?php echo htmlspecialchars($admin_pass_repeat??''); ?>">
						</td><td>
							<?php echo $ARnls['install:admin_pass_repeat_help']; ?>
						</td></tr>
					</table>

					<h2><?php echo $ARnls['install:install_modules']; ?></h2>
					<div class="field checkbox">
						<?php
							$disabled = '';
							$checked =  '';
							$svnsupport = check_svn();
							if (!$svnsupport) {
								$disabled = "disabled";
							} elseif ($enable_svn) {
								$checked = "checked='checked'";
							} else {
								$checked = '';
							}
						?>
						<input type="hidden" name="enable_svn" value="0">
						<input <?php echo $checked; ?> <?php echo $disabled; ?> type="checkbox" id="enable_svn" name="enable_svn" value="1">
						<label for='enable_svn'><?php echo $ARnls['install:enable_svn']; ?></label>
					</div>
					<div class="field checkbox">
						<?php
							$disabled = '';
							if ($database != "mysql") {
								$checked = '';
								$disabled = "disabled";
							} elseif ($enable_workspaces) {
								$checked = "checked='checked'";
							} else {
								$checked = '';
							}
						?>
						<input type="hidden" name="enable_workspaces" value="0">
						<input <?php echo $checked . ' ' . $disabled; ?> type="checkbox" id="enable_workspaces" name="enable_workspaces" value="1">
						<label for='enable_workspaces'><?php echo $ARnls['install:enable_workspaces']; ?></label>
					</div>
					<?php if (check_demo_ax()) { ?>
					<div class="field checkbox">
						<?php
							if ($install_demo) {
								$checked = "checked='checked'";
							} else {
								$checked = '';
							}
						?>
						<input type="hidden" name="install_demo" value="0">
						<input <?php echo $checked; ?> type="checkbox" id="install_demo" name="install_demo" value="1">
						<label for='install_demo'><?php echo $ARnls['install:install_demo']; ?></label>
					</div>
					<?php	}	?>
					<?php if (check_docs_ax()) { ?>
					<div class="field checkbox">
						<?php
							if ($install_docs) {
								$checked = "checked='checked'";
							} else {
								$checked = '';
							}
						?>
						<input type="hidden" name="install_docs" value="0">
						<input <?php echo $checked; ?> type="checkbox" id="install_docs" name="install_docs" value="1">
						<label for='install_docs'><?php echo $ARnls['install:install_docs']; ?></label>
					</div>
					<?php	}	?>
					<?php if (check_libs_ax()) { ?>
					<div class="field checkbox">
						<?php
							if ($install_libs) {
								$checked = "checked='checked'";
							} else {
								$checked = '';
							}
						?>
						<input type="hidden" name="install_libs" value="0">
						<input <?php echo $checked; ?> type="checkbox" id="install_libs" name="install_libs" value="1">
						<label for='install_libs'><?php echo $ARnls['install:install_libs']; ?></label>
					</div>
					<?php	}	?>
				</div>
			</div>
			<div class="buttons">
				<div class="right">
					<label class="button" for="previous"><?php echo $ARnls['install:previous']; ?></label>
					<input class="hidden" id="previous" type="submit" name="step" value="<?php echo $previousstep?>">
					<input class="hidden" id="current" type="submit" name="step" value="<?php echo $step?>">
					<label class="button" for="next"><?php echo $ARnls['install:next']; ?></label>
					<input class="hidden" id="next" type="submit" name="step" value="<?php echo $nextstep; ?>">
				</div>
			</div>
<?php include("step_footer.php"); ?>
