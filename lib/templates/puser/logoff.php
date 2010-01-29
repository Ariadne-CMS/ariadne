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
			<div class="logo">
				<img src="' . $AR->dir->www . '/images/tree/logo2.gif" alt="Ariadne Web Application Server">
				<span class="ariadne">Ariadne</span>
				<span class="ariadne_sub">Web Application Server</span>
			</div>Logged off<img class="typeicon" src="' . $AR->dir->www . 'images/icons/large/grants.png" alt="Login">
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
  if ($AR->user->data->login==$data->login || $AR->user->data->login=="admin") {
    if (!$checkedlocks) {
      ldRedirect("lock.user.report.phtml?arReturnPage=logoff.php?checkedlocks=true");
    } 
    else {
      $ARCurrent->session->kill();
      unset($ARCurrent->session);
      echo $pageStart;
      
      // Add nls for this phrase
      echo "Session closed for ".$data->name;
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