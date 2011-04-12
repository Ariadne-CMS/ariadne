<?php
	/******************************************************************
	 system.save.grantsarray.php                              Muze Ariadne
	 ------------------------------------------------------------------
	 Arguments: $path, $grants

	 Grant needed: config
	 Global variables: none

	 This template saves changes to grants defined at the current
	 object. It then updates the changes at the user/group object too.
	 
	 no result

	******************************************************************/
	if ($this->CheckLogin("config") && $this->CheckConfig()) {
		include_once($this->store->get_config("code")."modules/mod_grant.php");

		// some variable fetching stuff
		$userpath=$this->getvar("userpath");
		$grants=$this->getvar("grants");

		var_dump($userpath);
		var_dump($grants);

		if (!$userpath || !is_array($grants)) {
			$this->error=$ARnls["err:missingparam"];
		}
		if (!$this->error) {

			if ($userObj=current($this->get($userpath, "system.get.phtml"))) {
				if ($userObj->AR_implements("pgroup")) {
					$type="pgroup";
				} else if ($userObj->AR_implements("puser")) {
					$type="puser";
				} else {
					$this->error=sprintf($ARnls["err:nousergroup"],$userpath);
				}
				if (!$this->error) {
					if ($id=current($this->get($userpath, "system.get.login.phtml"))) {
						// first make sure that the object is clean (data can only be set via 
						// the defined interface: $arCallArgs)
						$this->data=current($this->get(".","system.get.data.phtml"));
						$this->data->config->grants[$type][$id] = $grants;
						$this->save($properties);
						$result=current($this->get($userpath, "system.save.grants.user.phtml", Array(
							"action"	=> "set",
							"path"		=> $this->path,
							"grants"	=> $grants
						)));
					} else {
						$this->error=sprintf($ARnls["err:notgetlogin"],$userpath);
					}
				}
			} else {
				$this->error=sprintf($ARnls["err:notfindusergroup"],$userpath);
			}
			if (!$this->error) {
				// clear public cache recursively
				$this->ClearCache($this->path, false, true);
			}
		}
	}
?>
