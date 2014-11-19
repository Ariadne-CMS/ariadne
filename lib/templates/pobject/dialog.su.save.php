<?php
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
        if ($this->CheckLogin("admin") && $this->CheckConfig()) {
		global $AR;
		$userpath = $this->getvar('target');

		if ($this->exists($userpath)) {
			$userob = current($this->get($userpath, "system.get.phtml"));
			$ARCurrent->session = false; // Make sure a new session ID is generated for the new user;
			ldSetCredentials($userob->data->login, $userob->parent);
		}
		ldRedirect($this->make_url());
	}
?>
