<?php
	if ($this->CheckSilent('read')) {
?>
<script type="text/javascript">
	function settime(name, form) {
		time = form[name+"_hour"].options[form[name+"_hour"].selectedIndex].value * 3600 +
			form[name+"_minute"].options[form[name+"_minute"].selectedIndex].value * 60;
		form[name].value = time;
	}
</script>
<table border="0" width="90%">
	<tr>
		<td>
		<?php
			if( !function_exists("input_time") ) {
				function input_time($name, $time) {

					if (!is_numeric($time)) {
						$date = getdate(time());
						$hours = $date['hours'];
						$mins = $date['minutes'];
						$time = $hours * 3600 + $mins * 60;
					} else {
						$hours = floor($time / 3600);
						$mins = floor(($time - ($hours * 3600)) / 60);
					}

					$mins = floor($mins / 5) * 5;

					echo '<input type="hidden" name="' . $name . '" value="' . $time . '">' . "\n";
					echo '<nobr><select name="' . $name . '_hour" onchange="settime(\'' . $name . '\', this.form);">' . "\n";

					for ($i = 0; $i <= 23; $i++) {
						echo '<option value="' . $i . '"'.($i == $hours ? " selected" : "").'>' . sprintf("%02d", $i) . '</option>' . "\n";
					}

					echo '</select>:' . "\n";
					echo '<select name="' . $name . '_minute" onchange="settime(\'' . $name . '\', this.form);">' . "\n";

					for ($i = 0; $i <= 59; $i = $i + 5) {
						echo '<option value="' . $i . '"'.($i == $mins ? " selected" : "").'>' . sprintf("%02d", $i) . '</option>' . "\n";
					}

					echo '</select></nobr>' . "\n";
				}
			}
		?>
			<fieldset id="selectstart">
				<legend><?php echo $ARnls['start']; ?></legend>
				<img src="<?php echo $AR->dir->images; ?>dot.gif" alt="" width="1" height="1"><br>
				<table border="0" align="center" width="90%">
					<tr>
						<td>
							<span class="required"><?php echo $ARnls['start']; ?></span>
						</td>
						<td>
							<span class="required"><?php echo $ARnls['time']; ?></span>
						</td>
					</tr>
					<tr>
						<td>
						<?php
							$startdate = $this->getdata('startdate', 'none');
							$enddate=$this->getdata('enddate', 'none');

							if (!$startdate) {
								if (!($startdate = $this->getdata('date'))) {
									$startdate = time();
								}
							}

							if ($enddate <= $startdate) {
								$enddate = $startdate;
							}

							$wgDateName = 'startdate';
							$wgDate = $startdate;

							if (!$wgDate) {
								$temp = getdate(time());
								$wgDate = mktime(0, 0, 0, $temp['mon'], $temp['mday'], $temp['year']);
							}

							$startdate = $wgDate;
							include($this->store->get_config('code') . 'widgets/date/js.html');
							include($this->store->get_config('code') . 'widgets/date/form.html');
						?>
						</td>
						<td>
						<?php input_time('starttime', $this->getdata('starttime', 'none')); ?>
						</td>
					</tr>
				</table>
			</fieldset>

			<fieldset id="selectend">
				<legend><?php echo $ARnls["end"]; ?></legend>
				<img src="<?php echo $AR->dir->images; ?>dot.gif" alt="" width="1" height="1"><br>
				<table border="0" align="center" width="90%">
					<tr>
						<td>
						<?php echo $ARnls['end']; ?>
						</td>
						<td>
						<?php echo $ARnls['time']; ?>
						</td>
					</tr>
					<tr>
						<td>
						<?php
							$wgDateName = 'enddate';
							$wgDate = $enddate;
							include($this->store->get_config('code') . 'widgets/date/js.html');
							include($this->store->get_config('code') . 'widgets/date/form.html');
						?>
						</td>
						<td>
						<?php input_time('endtime', $this->getdata('endtime','none')); ?>
						</td>
					</tr>
				</table>
			</fieldset>

			<fieldset id="scenarioeffect">
				<legend><?php echo $ARnls["effect"]; ?></legend>
				<img src="<?php echo $AR->dir->images; ?>dot.gif" alt="" width="1" height="1"><br>
				<table border="0" align="center">
					<tr>
						<td>
						<?php
							$scenario = $this->getdata('scenario', 'none');
							if ($this->exists($scenario)) {
								$this->get($scenario, 'show.effect.phtml');
							}
						?>
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
	</tr>
</table>
<?php
	}
?>
