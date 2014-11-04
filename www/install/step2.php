<?php
	include_once("getvars.php");
	include_once("nls." . $language . ".php");
	include_once("system_checks.php");
	include("step_header.php");
	$stepnum = substr($step, 4, strlen($step));
	$next = $stepnum+1;
	$previous = $stepnum-1;

	$nextstep = "step$next";
	$previousstep = "step$previous";
	$oddeven = "even";


?>
			<div id="sectiondata">
				<?php include("sections.php"); ?>
				<div id="tabs">
				</div>
				<div id="tabsdata">
					<h1><?php echo $ARnls['install:run_pre_install']; ?></h1>
					<p><?php echo $ARnls['install:pre_install_info']; ?></p>
					<div class="required">
						<h2><?php echo $ARnls['install:required_options']; ?></h2>
						<div class="info">
							<?php echo $ARnls['install:required_checks_info']; ?>
						</div>
						<table class="checks">
							<colgroup>
								<col class="col1">
								<col class="col2">
							</colgroup>
							<?php
								$req_checkresult = true;
								foreach ($required_checks as $check => $result) {
									$req_checkresult = $req_checkresult && $result;
									$oddeven = $oddeven == "odd" ? "even" : "odd";
									if ($result) {
										$resultstring = '<span class="passed">' . $ARnls['install:check_ok'] . '</span>';
									} else {
										$resultstring = '<span class="failed">' . $ARnls['install:check_failed'] . '</span>';
										$resultstring .= '<a target="_blank" href="http://www.ariadne-cms.org/docs/installation-requirements/#' . str_replace("check_", "", $check) . '">';
										$resultstring .= '<img class="helpicon" src="../images/icons/small/help.png" alt="' . $ARnls['install:help'] . '" title="' . $ARnls['install:help'] . '">';
										$resultstring .= '</a>';
									}
							?>
								<tr class="<?php echo $oddeven; ?>"><td><?php echo $ARnls['install:' . $check]; ?></td><td><?php echo $resultstring; ?></td></tr>
							<?php
								}
							?>
						</table>
						<?php
							if (!$req_checkresult) {
						?>
							<label class="button" for="recheck"><?php echo $ARnls['install:recheck']; ?></label>
							<input class="hidden" id="recheck" type="submit" name="step" value="<?php echo $step?>">
						<?php } ?>
					</div>
					<div class="recommended">
						<h2><?php echo $ARnls['install:recommended_options']; ?></h2>
						<div class="info">
							<?php echo $ARnls['install:recommended_options_info']; ?>
						</div>
						<table class="checks">
							<colgroup>
								<col class="col1">
								<col class="col2">
							</colgroup>
							<?php
								$rec_checkresult = true;
								foreach ($recommended_checks as $check => $result) {
									$rec_checkresult = $rec_checkresult && $result;
									$oddeven = $oddeven == "odd" ? "even" : "odd";
									if ($result) {
										$resultstring = '<span class="passed">' . $ARnls['install:check_ok'] . '</span>';
									} else {
										$resultstring = '<span class="failed">' . $ARnls['install:check_failed'] . '</span>';
										$resultstring .= '<a target="_blank" href="http://www.ariadne-cms.org/docs/installation-requirements/#' . str_replace("check_", "", $check) . '">';
										$resultstring .= '<img class="helpicon" src="../images/icons/small/help.png" alt="' . $ARnls['install:help'] . '" title="' . $ARnls['install:help'] . '">';
										$resultstring .= '</a>';
									}
							?>
								<tr class="<?php echo $oddeven; ?>"><td><?php echo $ARnls['install:' . $check]; ?></td><td><?php echo $resultstring; ?></td></tr>
							<?php
								}
							?>
						</table>
						<?php
							if (!$rec_checkresult) {
						?>
							<label class="button" for="recheck"><?php echo $ARnls['install:recheck']; ?></label>
							<input class="hidden" id="recheck" type="submit" name="step" value="<?php echo $step?>">
						<?php } ?>
					</div>
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