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
					<h1>Ariadne license</h1>
					<pre><?php include("license.php"); ?></pre>
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
