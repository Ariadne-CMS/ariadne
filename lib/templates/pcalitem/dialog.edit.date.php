<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {

		if ( !( $starttime ?? null ) ) {
			if ($data->starttime ?? null)	{
				$starttime=$data->starttime;
			} else {
				$starttime=time() ;
			}
		}
		$stime_arr=getdate($starttime);
		if ( !( $endtime ?? null ) ) {
			if ($data->endtime ?? null) {
				$endtime=$data->endtime;
				echo "<script>\n	endtime_changed=1;\n</script>";
			} else { // default is starttime + 1 hour.
				$endtime=mktime($stime_arr["hours"]+1,$stime_arr["minutes"],$stime_arr["seconds"],
								$stime_arr["mon"],$stime_arr["mday"],$stime_arr["year"]);
			}
		}
		$date=$this->getdata("date", "none");
		$etime_arr=getdate($endtime);
		if ( !( $date ?? null ) ) {
			$date=$starttime;
		}
		if ( !( $starthour ?? null ) ) {
			$starthour=$stime_arr["hours"];
		}
		if ( !( $startminute ?? null ) ) {
			$startminute=$stime_arr["minutes"];
		}
		if ( !( $endhour ?? null ) ) {
			$endhour=$etime_arr["hours"];
		}
		if ( !( $endminute ?? null ) ) {
			$endminute=$etime_arr["minutes"];
		}

		function ShowList($start, $end, $step, $selected) {
			$selected=round($selected / $step) * $step;
			for ($i=$start; $i<=$end; $i+=$step) {
				echo "<option value=\"$i\"";
				if ($i==$selected) {
					echo " selected";
				}
				echo ">$i\n";
			}
		}
?>
<script>
<!--

	starthour=<?php echo $starthour; ?>;
	startminute=<?php echo $startminute; ?>;
	endhour=<?php echo $endhour; ?>;
	endminute=<?php echo $endminute; ?>;

	function UpdateEndlists(form) {
		diffh=form.starthour.options[form.starthour.selectedIndex].value - starthour;
		diffm=form.startminute.options[form.startminute.selectedIndex].value - startminute;
		endhour=endhour+diffh;
		endminute=endminute+diffm;
		starthour=starthour+diffh;
		startminute=startminute+diffm;
		RedrawEndlists(form);
	}

	function UpdateEndtime(form) {
		diffh=form.endhour.options[form.endhour.selectedIndex].value - endhour;
		diffm=form.endminute.options[form.endminute.selectedIndex].value - endminute;
		endhour=endhour+diffh;
		endminute=endminute+diffm;
		RedrawEndlists(form);
	}

	function Select(list, value) {
		for (i=0; i<list.length; i++) {
			if (list.options[i].value==value) {
				list.options[i].selected=true;
				break;
			}
		}
	}

	function RedrawEndlists(form) {
		if (endhour<starthour) {
			endhour=starthour;
			endminute=startminute;
		}
		if (endhour==starthour && endminute<startminute) {
			endminute=startminute;
		}
		if (endminute>55) {
			endhour+=1;
			endminute=endminute-60;
		}
		if (endhour>23) {
			endhour=23;
			endminute=55;
		}
		Select(form.endhour, endhour);
		Select(form.endminute, endminute);
	}
//-->
</script>
<fieldset id="datefields">
	<legend><?php echo $ARnls["date"]; ?></legend>
	<div class="field">
		<label for="date"><?php echo $ARnls["date"]; ?></label>
	<?php
		$wgDateName="date";
		$wgDate=$date;
		include($this->store->get_config("code")."widgets/date/js.html");
		include($this->store->get_config("code")."widgets/date/form.html");
	?>
	</div>
	<div class="field">
		<label for="priority"><?php echo $ARnls["priority"]; ?></label>
		<select name="priority" id="priority">
			<?php
				$priority=$this->getdata("priority", "none");
				$this->ls("/system/calendar/priorities/", "show.option.value.phtml","selected=$priority");
			?>
		</select>

	</div>
</fieldset>
<fieldset id="time">
	<legend><?php echo $ARnls["time"]; ?></legend>
	<div class="field">
		<label for="starthour"><?php echo $ARnls["start"]; ?></label>
		<select id="starthour" name="starthour" onChange="UpdateEndlists(this.form);">
		<?php
				$start=0; $end=23; $step=1;
				ShowList($start,$end,$step,$starthour);
		?>
		</select> : <select id="startminute" name="startminute" onChange="UpdateEndlists(this.form);">
		<?php
				$start=0; $end=55; $step=5;
				ShowList($start,$end,$step,$startminute);
		?>
		</select>
	</div>
	<div class="field">
		<label for="endhour"><?php echo $ARnls["end"]; ?></label>
		<select id="endhour" name="endhour" onChange="UpdateEndtime(this.form);">
		<?php
				$start=0; $end=23; $step=1;
				ShowList($start,$end,$step,$endhour);
		?>
		</select> : <select id="endminute" name="endminute" onChange="UpdateEndtime(this.form);">
		<?php
				$start=0; $end=55; $step=5;
				ShowList($start,$end,$step,$endminute);
		?>
		</select>
	</div>
</fieldset>
<?php
	}
?>
