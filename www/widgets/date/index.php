<?php
  require("../../ariadne.inc");
  require($ariadne."/configs/ariadne.phtml");

  $date = $HTTP_GET_VARS["date"];
  if (!$date) {
    $date=time();
  }
  $format = $HTTP_GET_VARS["format"];
  if (!$format) {
    $format="%m-%d-%Y";  
  }
  $title = $HTTP_GET_VARS["title"];
  if (!$title) {
    $title="Select Date";
  }
  $name = $HTTP_GET_VARS["name"];
  if (!$name) {
    $name="date";
  }
  $args="&format=".RawUrlEncode($format)."&title=".RawUrlEncode($title)."&name=".RawUrlEncode($name);

?>
<html>
<head>
<title> 
<?php echo $title; ?>
</title>
<?php
  /*
    arguments: 
      $date - unix time stamp, month set will be shown, highlights this day.
  */
  // calculate first weekday of this month
  $date_arr=getdate($date);
  $starttime=mktime(0,0,0,$date_arr["mon"],1,$date_arr["year"]);
  $monthstart=getdate($starttime);
  $firstweekday=$monthstart["wday"];
  $endtime=mktime(0,0,0,$date_arr["mon"]+1,1,$date_arr["year"])-1;
  $monthend=getdate($endtime);
  $monthsize=$monthend["mday"];
  
  // is startdate in the current month -> highlight today.
  $today=getdate();
  if ($today["year"]==$date_arr["year"] && $today["mon"]==$date_arr["mon"]) {
    $current=$today["mday"];
  }
  $highlight=$date_arr["mday"];

?>

<style>
<!--
* {font-family:tahoma,arial,verdana;font-size: 11px;color:#666666}
.date {color:white ; font-weight:bold}
.day {background-color:#D6DFF7}
.table1{background-color:#215DC6}

A	{
	text-decoration: none;
	color: #003FEB;
}
A:hover {
	text-decoration: none;
	color: #428EFF; 
}
A.selected {
	border: inset 1px;
}
A.unselected {
	border: outset 1px;
}
A.cal { text-decoration:none; } 

-->
</style>
<script>
<!--
  function SetDate(date, formatted) {
    window.opener.SetDate('<?php echo $name; ?>', date, formatted);
    window.close();
  }
//-->
</script>

</head>
<body bgcolor="white">

<table border="0" cellspacing="0" cellpadding="0" class="table1">
  <tr> 
    <td rowspan="3"><img src="<?php echo $AR->dir->images; ?>dot.gif" width="1" height="1" alt=""></td>
    <td><a href="<?php echo $PHP_SELF; ?>?date=<?php 

			$mday = $date_arr["mday"];
			$newtime = mktime( 0, 0, 0, $date_arr["mon"]-1, $date_arr["mday"], $date_arr["year"]);
			$newtime_arr = getdate($newtime);
			if ($newtime_arr["mday"] < $mday) {
				$mday -= $newtime_arr["mday"];
			}
			echo mktime( 0, 0, 0, $date_arr["mon"]-1, $mday, $date_arr["year"]); 
			echo $args;
    ?>"><img src="<?php echo $AR->dir->images; ?>calendar/xp.prev.gif" alt="<" border="0"></a> 
      <font class="date">&nbsp; 
      <?php 
      echo $date_arr["month"]; 
    ?>
      &nbsp;</font><a href="<?php echo $PHP_SELF; ?>?date=<?php 

			$mday = $date_arr["mday"];
			$newtime = mktime( 0, 0, 0, $date_arr["mon"]+1, $date_arr["mday"], $date_arr["year"]);
			$newtime_arr = getdate($newtime);
			if ($newtime_arr["mday"] < $mday) {
				$mday = $mday - $newtime_arr["mday"];
			}
			echo mktime( 0, 0, 0, $date_arr["mon"]+1, $mday, $date_arr["year"]); 
			echo $args;
    ?>"><img src="<?php echo $AR->dir->images; ?>calendar/xp.next.gif" alt=">" border="0"></a> 
    </td>
    <td align="center">&nbsp;</td>
    <td align="right"><a href="<?php echo $PHP_SELF; ?>?date=<?php 
      echo mktime(0,0,0,$date_arr["mon"],$date_arr["mday"],$date_arr["year"]-1).$args; 
    ?>"><img src="<?php echo $AR->dir->images; ?>calendar/xp.prev.gif" alt="<" border="0"></a> 
      <font class="date">&nbsp; 
      <?php 
      echo $date_arr["year"]; 
    ?>
      &nbsp;</font><a href="<?php echo $PHP_SELF; ?>?date=<?php 
      echo mktime(0,0,0,$date_arr["mon"],$date_arr["mday"],$date_arr["year"]+1).$args; 
    ?>"><img src="<?php echo $AR->dir->images; ?>calendar/xp.next.gif" alt=">" border="0"></a></td>
    <td rowspan="3" class="trday"><img src="<?php echo $AR->dir->images; ?>dot.gif" width="1" height="1" alt=""></td>
  </tr>
  <tr> 
    <td bgcolor="white" colspan="3"> 
      <table border="0" cellspacing="2" cellpadding="2">
        <tr> 
          <td align="center" class="day"><font class="date">Sun</font><br>
            <img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
          <td class="day" align="center"><font class="date">Mon</font><br>
            <img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
          <td class="day" align="center"><font class="date">Tue</font><br>
            <img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
          <td class="day" align="center"><font class="date">Wed</font><br>
            <img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
          <td class="day" align="center"><font class="date">Thu</font><br>
            <img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
          <td class="day" align="center"><font class="date">Fri</font><br>
            <img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
          <td class="day" align="center"><font class="date">Sat</font><br>
            <img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
        </tr>
        <tr> 
          <?php
      for ($i=$firstweekday; $i; $i--) {
        echo "<td align=\"center\" valign=\"middle\"><a href=\"$PHP_SELF?date=".
          mktime(0,0,0,$date_arr["mon"],(-$i+1),$date_arr["year"]).$args.
          "\"><img src=\"".$AR->dir->images."dot.gif\" width=\"32\" height=\"20\" border=\"0\" alt=\"-$i\"></a></td>";
      }
      for ($i=1; $i<=$monthsize; $i++) {
	if ($i==$highlight) {
          echo "  <td bgcolor=\"#CCCCCC\">";
        } else {
          echo "  <td bgcolor=\"#EEEEEE\">";
        }
        
	$idate=mktime(0,0,0,$date_arr["mon"],$i,$date_arr["year"]);
        if ($i==$current) {
          echo "<u><a class=\"current\" href=\"javascript:SetDate($idate,'".
            strftime($format, $idate)."');\">$i</a></u>";
        } else {
          echo "<a class=\"cal\" href=\"javascript:SetDate($idate,'".
            strftime($format, $idate)."');\">$i</a>";
        }
        echo "</td>\n";
        if ((($i+$firstweekday)%7)==0) { // end of the week, next row
          echo "</tr>";
          if ($i<$monthsize) { // only start a new row if there are more days
            echo "<tr>\n";
          }
        }
      }
      $date_arr=getdate(mktime(0,0,0,$date_arr["mon"]+1,1,$date_arr["year"]));
      $firstweekday=$date_arr["wday"]; // first week day of next month
      if ($firstweekday) { // skip if first weekday of next month is sunday
        for ($i=1; $i<(8-$firstweekday); $i++) {
          echo "<td align=\"center\" valign=\"middle\"><a href=\"$PHP_SELF?date=".
            mktime(0,0,0,$date_arr["mon"],$i,$date_arr["year"]).$args.
            "\"><img src=\"".$AR->dir->images."dot.gif\" width=\"32\" height=\"20\" border=\"0\" alt=\"$i\"></a></td>";
        }
      }
    ?>
        </tr>
      </table>
    </td>
  </tr>
  <tr> 
    <td colspan="3"><img src="<?php echo $AR->dir->images; ?>dot.gif" width="1" height="1" alt=""></td>
  </tr>
</table>
<form>
  <table width="100%" border="0">
  <tr><td align="center">
    <input type="button" value="Cancel" onClick="window.close();">
  </td></tr>
  </table>
</form>
</body>
</html>