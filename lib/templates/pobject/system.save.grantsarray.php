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

		if (!$userpath || !is_array($grants)) {
			$this->error=$ARnls["err:missingparam"];
		}
		if (!$this->error) {
			// first make sure that the object is clean (data can only be set via 
			// the defined interface: $arCallArgs)
			$this->data=current($this->get(".","system.get.data.phtml"));

			if ($type=current($this->get($userpath, "system.get.type.phtml"))) {
				if ($this->store->implements($type, "pgroup")) {
					$type="pgroup";
				} else if ($this->store->implements($type, "puser")) {
					$type="puser";
				} else {
					$this->error=sprintf($ARnls["err:nousergroup"],$userpath);
				}
				if (!$this->error) {
					if ($id=current($this->get($userpath, "system.get.login.phtml"))) {
						$this->data->config->grants[$type][$id] = $grants;
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
				$this->save($properties);
				// clear public cache recursively
				$this->ClearCache($this->path, false, true);
			}
		}
	}
?>
