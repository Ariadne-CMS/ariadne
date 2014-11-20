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
					<h1><?php echo $ARnls['install:database_configuration']; ?></h1>
					<p><?php echo $ARnls['install:database_info']; ?></p>
					<table class="db_basic">
						<colgroup>
							<col class="col1">
							<col class="col2">
						</colgroup>
						<tr class="odd"><td>
							<label for="database"><?php echo $ARnls['install:select_database']; ?></label>
							<select id="database" name="database">
							<?php
								foreach ($databases as $key => $label) {
									if ($database == $key) {
										$selected = ' selected';
									} else {
										$selected = '';
									}
							?>
								<option<?php echo $selected; ?> value="<?php echo $key; ?>"><?php echo $label; ?></option>
							<?php
								}
							?>
							</select>
							</td>
							<td><?php echo $ARnls['install:select_database_help']; ?></td>
						</tr>
						<tr class="even"><td>
							<label for="database_host"><?php echo $ARnls['install:database_host']; ?></label>
							<input class="text" type="text" id="database_host" name="database_host" value="<?php echo $database_host; ?>">
						</td><td>
							<?php echo $ARnls['install:database_host_help']; ?>
						</td></tr>
						<tr class="odd"><td>
							<label for="database_user"><?php echo $ARnls['install:database_user']; ?></label>
							<input class="text" type="text" id="database_user" name="database_user" value="<?php echo $database_user; ?>">
						</td><td>
							<?php echo $ARnls['install:database_user_help']; ?>
						</td></tr>
						<tr class="even"><td>
							<label for="database_pass"><?php echo $ARnls['install:database_pass']; ?></label>
							<input class="text" type="password" id="database_pass" name="database_pass" value="<?php echo $database_pass; ?>">
						</td><td>
							<?php echo $ARnls['install:database_pass_help']; ?>
						</td></tr>
						<tr class="odd"><td>
							<label for="database_name"><?php echo $ARnls['install:database_name']; ?></label>
							<input class="text" type="text" id="database_name" name="database_name" value="<?php echo $database_name; ?>">
						</td><td>
							<?php echo $ARnls['install:database_name_help']; ?>
						</td></tr>
					</table>
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
