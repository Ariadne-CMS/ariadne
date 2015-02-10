<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 
<html>
	<head>
		<title><?php echo $ARnls['aboutariadne']; ?></title>
		<link rel="stylesheet" type="text/css" href="<?php echo $AR->dir->www; ?>styles/login.css">
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
	</head>
<body id="login_panel">
	<div id="centered">
		<div id="header">
			<div class="logo">
				<img src="<?php echo $AR->dir->www; ?>/images/tree/logo2.gif" alt="Ariadne Web Application Server">
				<span class="ariadne">Ariadne</span>
				<span class="ariadne_sub">Web Application Server</span>
			</div>
		</div>
		<div id="sectiondata" class="nosections notfixed">
			<div id="tabs"></div>
			<div id="tabsdata">
				<div id="data">
					<div id="version">
						Version 8.5<br>
						feb 10; 2015<br><br>
						&copy; 1998 - 2015 <a href="http://www.muze.nl/" target="_blank">Muze</a>
					</div>
					<form action="">
						<div>
							<input type="button" name="ok" value="<?php echo $ARnls["ok"]; ?>" onClick="window.close()">
						</div>
					</form>
					<div id="disclaimer">
						Ariadne comes with ABSOLUTELY NO WARRANTY. Ariadne is free software, you are welcome to redistribute it under certain <a href="help.about.license.php" target="_blank">conditions</a>.<br>&nbsp;</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
