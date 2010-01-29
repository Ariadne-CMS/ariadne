<?php
	$explain = array(
		0 => array(
			"object" => array(
				"path" => "/sites/mysite/",
				"type" => "psite"
			),
			"owner" => array(
				"path" => "/system/groups/muze/",
				"type" => "pgroup",
				"name" => "muze"
			),
			"grants" => array(
				"string" => "add edit read"
			)
		),
		1 => array(
			"object" => array(
				"path" => "/sites/mysite/",
				"type" => "psite"
			),
			"owner" => array(
				"path" => "/system/users/yvo/",
				"type" => "puser",
				"name" => "yvo"
			),
			"grants" => array(
				"string" => "add delete read"
			)
		),
		2 => array(
			"object" => array(
				"path" => "/sites/mys...ntact",
				"type" => "pdir"
			),
			"owner" => array(
				"path" => "/system/users/yvo/",
				"type" => "puser",
				"name" => "yvo"
			),
			"grants" => array(
				"string" => ">delete add read and a lot of other grants"
			)
		),

	);

	$grantsList = Array();

	$userPath = $this->getvar('selecteduser');
	if ($this->exists($userPath)) {
		$user = current($this->get($userPath, 'system.get.phtml'));
		$userName = $user->data->name;
		$userGroupPaths = $user->data->groups;
		if (!is_Array($userGroupPaths)) {
			$userGroupPaths = Array();
		}
		if (!$userGroupPaths['/system/groups/public/'] && !$user->implements('pgroup')) {
			$userGroupPaths['/system/groups/public'] = '/system/groups/public/';
		}
		$userGroups = Array();
		foreach ($userGroupPaths as $userGroupPath) {
			$group = current($this->get($userGroupPath, 'system.get.phtml'));
			$group->active = true;
			$userGroups[$userGroupPath] = $group;
		}

		$allInactive = false;
		$grantsPathPrev = '';
		$grantsPath = $this->getvar('selectedpath');
		$grantsList = Array();
		do {
			$object = current($this->get($grantsPath, 'system.get.phtml'));
			if ($user->data->config->usergrants[$grantsPath]) {
				array_unshift(
					$grantsList,
					Array(
						'active'	=> !$allInactive,
						'object'	=> Array(
							'path'		=> $object->path,
							'type'		=> $object->type
						),
						'owner'		=> Array(
							'name'		=> $user->data->name,
							'path'		=> $user->path,
							'type'		=> $user->type
						),
						'grants'	=> Array(
							'array'		=> $user->data->config->usergrants[$grantsPath],
							'string'	=> grantsArrayToString($user->data->config->usergrants[$grantsPath])
						)
					)
				);
				$allInactive = true;
			}

			foreach ($userGroups as $group) {
				if ($group->data->config->usergrants[$grantsPath]) {
					array_unshift(
						$grantsList,
						Array(
							'active'	=> $group->active & !$allInactive,
							'object'	=> Array(
								'path'		=> $object->path,
								'type'		=> $object->type
							),
							'owner'		=> Array(
								'name'		=> $group->data->name,
								'path'		=> $group->path,
								'type'		=> $group->type
							),
							'grants'	=> Array(
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

//	print_r($grantsList);
?>
	<div class="explain">
		<h2><?php echo $ARnls['ariadne:grants:grants_explained']; echo $userName; ?></h2>
			<?php	foreach($grantsList as $rule) {	?>
						<div class="item">
							<img src="<?php echo $this->call('system.get.icon.php', array('type' => $info['type'], 'size' => 'medium')); ?>" alt="<?php echo $rule['object']['type']; ?>">
							<?php echo labelspan($rule['object']['path'], 16); ?><br>
							<div class="owner">
								<img class="explain_owner" src="<?php echo $AR->dir->www; ?>images/icons/small/<?php echo $rule['owner']['type']; ?>" alt="<?php echo $rule['owner']['type']; ?>">
								<?php echo labelspan($rule['owner']['name'], 12); ?>
							</div>
							<?php echo $rule['grants']['string']; ?>
						</div>
			<?php	}	?>
	</div>
