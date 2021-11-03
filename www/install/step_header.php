<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo $ARnls['install:install_ariadne']; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="install.css" rel="stylesheet" type="text/css">
		<link href="../styles/wizard.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<form action="index.php" method="POST">
			<?php include("keepvars.php"); ?>
			<div id="header">
				<div class="logo">
					<img src="../images/tree/logo2.gif" alt="Ariadne Web Application Server">
					<span class="ariadne">Ariadne</span>
					<span class="ariadne_sub">Web Application Server</span>
				</div>
				<span class="text"><?php echo $ARnls['install:install_ariadne']; ?></span>
				<img class="typeicon" src="../images/icons/large/grants.png" alt="<?php echo $ARnls['install:install_ariadne']; ?>">
			</div>
