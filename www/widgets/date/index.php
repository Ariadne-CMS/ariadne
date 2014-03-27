<?php
	$name = $_GET["name"];
	$date = $_GET["date"];
	$title = $_GET["title"];
	$format = $_GET["format"];

	$name = preg_replace("/[^[\]A-Za-z0-9: _-]/", '', $name);
	$title = preg_replace("/[^A-Za-z0-9 _-]/", '', $title);
	$date = preg_replace("[^0-9]", '', $date);
	$format = preg_replace("/[^%mdYy-]/", '', $format);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>


    <meta http-equiv="content-type" content="text/html; charset=utf-8">
<title><?php echo htmlspecialchars($title); ?></title>

<style type="text/css">
/*margin and padding on body element
  can introduce errors in determining
  element position and are not recommended;
  we turn them off as a foundation for YUI
  CSS treatments. */
body {
	margin:0;
	padding:0;
}
#cal1Container {
	margin-top: 20px;
	margin-left: 46px;
}

</style>

<link rel="stylesheet" type="text/css" href="../../js/yui/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="../../js/yui/calendar/assets/skins/sam/calendar.css" />
<script type="text/javascript" src="../../js/yui/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="../../js/yui/calendar/calendar-min.js"></script>

<!--there is no custom header content for this example-->

</head>

<body class="yui-skin-sam">
<div id="cal1Container"></div>
<script type="text/javascript">
	function zeroFill( number, width ) {
		width -= number.toString().length;
		if ( width > 0 )  {
			return new Array( width + (/\./.test( number ) ? 2 : 1) ).join( '0' ) + number;
		}
		return number;
	}

	function formatDate(date, format) {
		result = format;
		result = result.replace("%m", zeroFill(date.getMonth() + 1, 2));
		result = result.replace("%d", zeroFill(date.getDate(), 2));
		result = result.replace("%Y", date.getFullYear());
		result = result.replace("%y", date.getFullYear().toString().substr(2,2));
		return result;
	}

	YAHOO.util.Event.onDOMReady(function() {
		var calendar = new YAHOO.widget.Calendar("cal1", "cal1Container");
		var selectDate = new Date(<?php echo htmlspecialchars($date); ?> * 1000);
		calendar.select(selectDate);
		calendar.setMonth(selectDate.getMonth());
		calendar.setYear(selectDate.getFullYear());
		calendar.render();
		calendar.selectEvent.subscribe(function() {
			if (calendar.getSelectedDates().length > 0) {
				var selDate = calendar.getSelectedDates()[0];
				var date = selDate.getTime() / 1000;
				var formatted = formatDate(selDate, '<?php echo htmlspecialchars($format); ?>');
				window.opener.SetDate('<?php echo htmlspecialchars($name); ?>', date, formatted);
				window.close();
			}
		}, calendar, true);
	});
</script>

<div style="clear:both" ></div>

</body>
</html>
