<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {

		$roptions = array(
				"none" 	=> $ARnls["none"],
				"day"  	=> $ARnls["day"],
				"week" 	=> $ARnls["week"],
				"month"	=> $ARnls["month"],
				"year"	=> $ARnls["year"]
		);
		$repeat=$this->getdata("repeat", "none");

		$repeatn=$this->getdata("repeatn","none");
		if (!$repeatn) {
			$repeatn=1;
		}
		$repeatend=$this->getdata("repeatend","none");
		if (!$repeatend) {
			$repeatend=$this->getdata("date", "none") + $this->getdata("starthour","none")*3600 + $this->getdata("startminute", "none") * 60;
		}
		$repeat_on=$this->getdata("repeat_on", "none");
		if ( !$repeat_on ) {
			$weekday = date('w', $this->getdata('date'));
			$repeat_on[$weekday] = true;
		}
		$repeat_by=$this->getdata("repeat_by", "none");
		if (!$repeat_by) {
			$repeat_by="day";
		}
		$woptions=array(
			$ARnls["sun"],
			$ARnls["mon"],
			$ARnls["tue"],
			$ARnls["wed"],
			$ARnls["thu"],
			$ARnls["fri"],
			$ARnls["sat"]
		);


?>
<script>
  var currentRepeat=false;

  function updateRepeat(value) {
	if (!value) {
		repeat=document.wgWizForm.repeat;
		value=repeat.options[repeat.selectedIndex].value;
	}
    if (window.all) {
      var fieldset=window.all[value];
    } else {
      var fieldset=document.getElementById(value);
    }
    if (currentRepeat) {
      currentRepeat.style.display='none';
	}
    fieldset.style.display='block';
	currentRepeat=fieldset;
  }

  function updateRepeatN(value) {
	document.wgWizForm.repeatn.value=value;
	document.wgWizForm.day_repeatn.value=value;
	document.wgWizForm.week_repeatn.value=value;
	document.wgWizForm.month_repeatn.value=value;
	document.wgWizForm.year_repeatn.value=value;
  }

  var defaultSetDate = SetDate;
  // alternative SetDate for the date widget
  SetDate = function(name, date, formatted) {
	if (defaultSetDate) {
		if (!/_repeatend$/.test( name )) {
			return defaultSetDate( name, date, formatted );
		}
	}
    document.wgWizForm.repeatend.value=date;
    document.wgWizForm['day_repeatend'].value=date;
    document.wgWizForm['formattedday_repeatend'].value=formatted;
    document.wgWizForm['week_repeatend'].value=date;
    document.wgWizForm['formattedweek_repeatend'].value=formatted;
    document.wgWizForm['month_repeatend'].value=date;
    document.wgWizForm['formattedmonth_repeatend'].value=formatted;
    document.wgWizForm['year_repeatend'].value=date;
    document.wgWizForm['formattedyear_repeatend'].value=formatted;
  }
  function init_repeat() {
	<?php
		if ($repeat) {
			echo "		updateRepeat('".$repeat."');\n";
		}
	?>
  }
  YAHOO.util.Event.onDOMReady(init_repeat)

</script>
<fieldset id="data">
	<legend><?php echo $ARnls["repeat"]; ?></legend>
	<div class="field">
		<label for="repeat"><?php echo $ARnls["repeat"]; ?></label>
		<select id="repeat" name="repeat" onChange="updateRepeat();" class="wgWizAutoFocus">
		<?php
			foreach( $roptions as $key => $val ) {
				echo "<option value=\"$key\"";
				if ((!$repeat && $key=="none") || ($repeat==$key)) { echo " selected"; }
				echo ">$val</option>";
			}
		?>
		</select>
	</div>
</fieldset>
<div>
	<input type="hidden" name="repeatn" value="<?php echo $repeatn; ?>">
	<input type="hidden" name="repeatend" value="<?php echo $repeatend; ?>">
	<input type="hidden" name="repeat_on" value="<?php echo $repeat_on; ?>">
</div>

<fieldset id="day" style="display: none;">
	<legend><?php echo $ARnls["repeat"].": ".$ARnls["day"]; ?></legend>
	<div class="field">
		<label for="day_repeatn"><?php echo $ARnls["every"]; ?></label>
		<input size="3" maxlength="3" type="text" name="day_repeatn" value="<?php echo $repeatn; ?>" onChange="updateRepeatN(this.value);">&nbsp;<?php echo $ARnls["days"]; ?>
	</div>
	<div class="field">
		<label for="day_repeatend"><?php echo $ARnls["endson"]; ?></label>
		<?php
			$wgDate=$repeatend;
			$wgDateName="day_repeatend";
			include($this->store->get_config("code")."widgets/date/js.selectdate.html");
			include($this->store->get_config("code")."widgets/date/form.html");
		?>
	</div>
</fieldset>

<fieldset id="week" style="display: none;">
	<legend><?php echo $ARnls["repeat"].": ".$ARnls["week"]; ?></legend>
	<div class="field">
		<label for="week_repeatn"><?php echo $ARnls["every"]; ?></label>
		<input size="3" maxlength="3" type="text" name="week_repeatn" value="<?php echo $repeatn; ?>" onChange="updateRepeatN(this.value);">&nbsp;<?php echo $ARnls["weeks"]; ?>
	</div>
	<div class="field">
		<label><?php echo $ARnls["repeaton"]; ?></label>
		<table><tr>
		<?php
			foreach( $woptions as $key => $option ) {
				echo "<td><input type=\"checkbox\" name=\"repeat_on[$key]\" value=\"1\"";
				if ($repeat_on[$key]) {
					echo " checked";
						}
						echo ">&nbsp;$option</td>";
					}
					?>
			</tr>
		</table>
	</div>
	<div class="field">
		<label for="week_repeatend"><?php echo $ARnls["endson"]; ?></label>
		<?php
			$wgDate=$repeatend;
			$wgDateName="week_repeatend";
			include($this->store->get_config("code")."widgets/date/form.html");
		?>
	</div>
</fieldset>

<fieldset id="month" style="display: none;">
	<legend><?php echo $ARnls["repeat"].": ".$ARnls["month"]; ?></legend>
	<div class="field">
		<label for="month_repeatn"><?php echo $ARnls["every"]; ?></label>
		<input size="3" maxlength="3" type="text" name="month_repeatn" value="<?php echo $repeatn; ?>" onChange="updateRepeatN(this.value);">&nbsp;<?php echo $ARnls["months"]; ?>
	</div>
	<div class="field">
		<label for="repeat_by"><?php echo $ARnls["repeatby"]; ?></label>
		<input type="radio" name="repeat_by" value="<?php echo $ARnls["day"]; ?>"
				<?php if ($repeat_by=="day")  echo "checked"; ?>>
				<?php echo $ARnls["day"]; ?>
		<input type="radio" name="repeat_by" value="<?php echo $ARnls["date"]; ?>"
				<?php if ($repeat_by=="date")  echo "checked"; ?>>
				<?php echo $ARnls["date"]; ?>
	</div>
	<div class="field">
		<label for="month_repeatend">
		<?php
			$wgDate=$repeatend;
			$wgDateName="month_repeatend";
			include($this->store->get_config("code")."widgets/date/form.html");
		?>
	</div>
</fieldset>

<fieldset id="year" style="display: none;">
	<legend><?php echo $ARnls["repeat"].": ".$ARnls["year"]; ?></legend>
	<div class="field">
		<label for="year_repeatn"><?php echo $ARnls["every"]; ?></label>
		<input size="3" maxlength="3" type="text" name="year_repeatn" value="<?php echo $repeatn; ?>" onChange="updateRepeatN(this.value);">&nbsp;<?php echo $ARnls["years"]; ?>
	</div>
	<div class="field">
		<label for="year_repeatend"><?php echo $ARnls["endson"]; ?></label>
		<?php
			$wgDate=$repeatend;
			$wgDateName="year_repeatend";
			include($this->store->get_config("code")."widgets/date/form.html");
		?>
	</div>
</fieldset>
</script>
<?php
	}
?>
