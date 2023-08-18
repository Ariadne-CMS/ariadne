<?php


$pageStart = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Ariadne - Access denied</title>
		<link rel="stylesheet" type="text/css" href="' . $AR->dir->www . 'styles/login.css">
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
	</head>
<body id="login_panel">
	<div id="centered">
		<div id="header">
';

$pageSubHeader = '
			Logged off<img class="typeicon" src="' . $AR->dir->www . 'images/icons/large/grants.png" alt="Login">
		</div>
		<div id="sectiondata" class="nosections notfixed">
			<div id="tabs"></div>
			<div id="tabsdata">
				<div id="data">
';

$pageEnd = '
				</div>
			</div>
		</div>
	</div>
</body>
</html>
';

$loginForm = '
<form action="'.$AR->dir->www.'">
	<div class="field">
		<input type="submit" value="'.$ARnls["login"].'">
	</div>
</form>
';

$ARCurrent->arLoginSilent=1;
if ($this->CheckLogin("read")) {
  if ( ( isset( $data->login ) && $AR->user->data->login==$data->login ) || $this->CheckAdmin($AR->user) ) {
    if (!$checkedlocks) {
      ldRedirect("dialog.locks.php?arReturnPage=logoff.php?checkedlocks=true");
    }
    else {
      $ARCurrent->session->kill();
      unset($ARCurrent->session);
      echo $pageStart;
      ar::call('ariadne.logo.html');
      echo $pageSubHeader;

      // Add nls for this phrase
      echo "Session closed for ".$AR->user->data->name;
      echo $loginForm;
      echo $pageEnd;
    }
  }
  else {
    // Add nls for this phrase
    echo "<h2>Sorry, you can only logoff yourself.</h2>";
  }
}
else {
  echo $pageStart;

  // Add nls for this phrase
  echo "Session closed";
  echo $loginForm;
  echo $pageEnd;
}
?>