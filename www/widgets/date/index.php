<?php
  require("../../ariadne.inc");
  require($ariadne."/configs/ariadne.phtml");
  if (!$date) {
    $date=time();
  }
  if (!$format) {
    $format="%m-%d-%Y";  
  }
  if (!$title) {
    $title="Select Date";
  }
  if (!$name) {
    $name="date";
  }
  $args="&format=".RawUrlEncode($format)."&title=".RawUrlEncode($title)."&name=".RawUrlEncode($name);
?><html>
<head>
<title><?php echo $title; ?></title>
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
  A.cal { text-decoration:none; } -->
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
<table border="0" cellspacing="0" cellpadding="0" bgcolor="#404074">
<tr>
  <td rowspan="3"><img src="<?php echo $AR->dir->images; ?>dot.gif" width="1" height="1" alt=""></td>
  <td><a href="<?php echo $PHP_SELF; ?>?date=<?php 
      echo mktime(0,0,0,$date_arr["mon"]-1,$date_arr["mday"],$date_arr["year"]).$args; 
    ?>"><img src="<?php echo $AR->dir->images; ?>calendar/prev.gif" alt="<" border="0"></a>
      <font face="arial, helvetica" size="2" color="white"><b>&nbsp;<?php 
      echo $date_arr["month"]; 
    ?>&nbsp;</b></font>
      <a href="<?php echo $PHP_SELF; ?>?date=<?php 
      echo mktime(0,0,0,$date_arr["mon"]+1,$date_arr["mday"],$date_arr["year"]).$args; 
    ?>"><img src="<?php echo $AR->dir->images; ?>calendar/next.gif" alt=">" border="0"></a>
  </td>
  <td align="center">&nbsp;</td>
  <td align="right"><a href="<?php echo $PHP_SELF; ?>?date=<?php 
      echo mktime(0,0,0,$date_arr["mon"],$date_arr["mday"],$date_arr["year"]-1).$args; 
    ?>"><img src="<?php echo $AR->dir->images; ?>calendar/prev.gif" alt="<" border="0"></a>
      <font face="arial, helvetica" size="2" color="white"><b>&nbsp;<?php 
      echo $date_arr["year"]; 
    ?>&nbsp;</b></font>
      <a href="<?php echo $PHP_SELF; ?>?date=<?php 
      echo mktime(0,0,0,$date_arr["mon"],$date_arr["mday"],$date_arr["year"]+1).$args; 
    ?>"><img src="<?php echo $AR->dir->images; ?>calendar/next.gif" alt=">" border="0"></a></td>
  <td rowspan="3"><img src="<?php echo $AR->dir->images; ?>dot.gif" width="1" height="1" alt=""></td>
</tr><tr>
  <td bgcolor="white" colspan="3">
    <table border="0" cellspacing="2" cellpadding="2">
    <tr>
      <td bgcolor="#404074" align="center"><font face="arial, helvetica" size="2" 
        color="white"><b>Sun</b></font><br><img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
      <td bgcolor="#404074" align="center"><font face="arial, helvetica" size="2" 
        color="white"><b>Mon</b></font><br><img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
      <td bgcolor="#404074" align="center"><font face="arial, helvetica" size="2" 
        color="white"><b>Tue</b></font><br><img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
      <td bgcolor="#404074" align="center"><font face="arial, helvetica" size="2" 
        color="white"><b>Wed</b></font><br><img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
      <td bgcolor="#404074" align="center"><font face="arial, helvetica" size="2" 
        color="white"><b>Thu</b></font><br><img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
      <td bgcolor="#404074" align="center"><font face="arial, helvetica" size="2" 
        color="white"><b>Fri</b></font><br><img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
      <td bgcolor="#404074" align="center"><font face="arial, helvetica" size="2" 
        color="white"><b>Sat</b></font><br><img src="<?php echo $AR->dir->images; ?>dot.gif" width="32" height="1"></td>
    </tr><tr>
    <?php
      for ($i=$firstweekday; $i; $i--) {
        echo "<td align=\"center\" valign=\"middle\"><a href=\"$PHP_SELF?date=".
          mktime(0,0,0,$date_arr["mon"],(-$i+1),$date_arr["year"]).
          "\"><img src=\"".$AR->dir->images."dot.gif\" width=\"32\" height=\"20\" border=\"0\" alt=\"-$i\"></a></td>";
      }
      for ($i=1; $i<=$monthsize; $i++) {
	if ($i==$highlight) {
          echo "  <td bgcolor=\"#CCCCCC\">";
        } else {
          echo "  <td bgcolor=\"#EEEEEE\">";
        }
        echo "<font face=\"arial, helvetica\" size=\"2\">";
	$idate=mktime(0,0,0,$date_arr["mon"],$i,$date_arr["year"]);
        if ($i==$current) {
          echo "<u><a class=\"current\" href=\"javascript:SetDate($idate,'".
            strftime($format, $idate)."');\">$i</a></u>";
        } else {
          echo "<a class=\"cal\" href=\"javascript:SetDate($idate,'".
            strftime($format, $idate)."');\">$i</a>";
        }
        echo "</font></td>\n";
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
            mktime(0,0,0,$date_arr["mon"],$i,$date_arr["year"]).
            "\"><img src=\"".$AR->dir->images."dot.gif\" width=\"32\" height=\"20\" border=\"0\" alt=\"$i\"></a></td>";
        }
      }
    ?>
    </tr>
    </table>
  </td>
</tr><tr>
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