<?php
	include_once("getvars.php");
	include_once("nls." . $language . ".php");
	include("step_header.php");
?>
			<div id="sectiondata">
				<?php include("sections.php"); ?>
				<div id="tabs">
				</div>
				<div id="tabsdata">
					<h1><?php echo $ARnls['install:welcome']; ?></h1>
					<p><?php echo $ARnls['install:selectlanguage']; ?></p>
						<select name="language">
							<?php
								foreach ($languages as $key => $name) {
									if ($language == $key) {
										$selected = " selected";
									} else {
										$selected = "";
									}
							?>
							<option<?php echo $selected; ?> value="<?php echo $key; ?>"><?php echo $name; ?></option>
							<?php
								}
							?>
						</select>
				</div>
			</div>
			<div class="buttons">
				<div class="right">
					<label class="button" for="next"><?php echo $ARnls['install:next']; ?></label>
					<input class="hidden" id="next" type="submit" name="step" value="step2">
				</div>
			</div>
<?php	include("step_footer.php"); ?>
