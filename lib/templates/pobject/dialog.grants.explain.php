<?php

	include_once($this->store->get_config("code")."modules/mod_yui.php");

	$grantsList = array();

	$userPath = $this->getvar('selecteduser');
	if ($this->exists($userPath)) {
		$user = current($this->get($userPath, 'system.get.phtml'));
		$userName = $user->data->name;
		$userGroupPaths = $user->data->groups??null;
		if (!is_array($userGroupPaths)) {
			$userGroupPaths = array();
		}
		if (!($userGroupPaths['/system/groups/public/']??null) && !$user->implements('pgroup')) {
			$userGroupPaths['/system/groups/public'] = '/system/groups/public/';
		}
		$userGroups = array();
		foreach ($userGroupPaths as $userGroupPath) {
			$group = current($this->get($userGroupPath, 'system.get.phtml'));
			if ($group) {
				$group->active = true;
				$userGroups[$userGroupPath] = $group;
			}
		}

		$allInactive = false;
		$grantsPathPrev = '';
		$grantsPath = $this->getvar('selectedpath');
		$grantsList = array();
		do {
			$object = current($this->get($grantsPath, 'system.get.phtml'));
			if ($user->data->config->usergrants[$grantsPath]??null) {
				array_unshift(
					$grantsList,
					array(
						'active'	=> !$allInactive,
						'object'	=> array(
							'path'		=> $object->path,
							'type'		=> $object->type
						),
						'owner'		=> array(
							'name'		=> $user->data->name,
							'path'		=> $user->path,
							'type'		=> $user->type
						),
						'grants'	=> array(
							'array'		=> $user->data->config->usergrants[$grantsPath],
							'string'	=> grantsArrayToString($user->data->config->usergrants[$grantsPath])
						)
					)
				);
				$allInactive = true;
			}

			foreach ($userGroups as $group) {
				if ($group->data->config->usergrants[$grantsPath]??null) {
					array_unshift(
						$grantsList,
						array(
							'active'	=> $group->active & !$allInactive,
							'object'	=> array(
								'path'		=> $object->path,
								'type'		=> $object->type
							),
							'owner'		=> array(
								'name'		=> $group->data->name,
								'path'		=> $group->path,
								'type'		=> $group->type
							),
							'grants'	=> array(
								'array'		=> $group->data->config->usergrants[$grantsPath],
								'string'	=> grantsArrayToString($group->data->config->usergrants[$grantsPath])
							)
						)
					);
					$group->active = false;
				}
			}

			$grantsPathPrev = $grantsPath;
			$grantsPath = $this->make_path($grantsPath, '..');
		} while ($grantsPath != $grantsPathPrev);
	}
?>
	<div class="explain">
		<h2><?php echo $ARnls['ariadne:grants:grants_explained']; echo $userName; ?></h2>
			<?php	foreach($grantsList as $rule) {	?>
						<div class="item">
							<img src="<?php echo $this->call('system.get.icon.php', array('type' => $info['type'], 'size' => 'medium')); ?>" alt="<?php echo $rule['object']['type']; ?>">
							<?php echo yui::labelspan($rule['object']['path'], 16); ?><br>
							<div class="owner">
								<img class="explain_owner" src="<?php echo $this->call('system.get.icon.php', array('type' => $rule['owner']['type'], 'size' => 'small')); ?>" alt="<?php echo $rule['owner']['type']; ?>">
								<?php echo yui::labelspan($rule['owner']['name'], 12); ?>
							</div>
							<?php echo $rule['grants']['string']; ?>
						</div>
			<?php	}	?>
	</div>
