<?php

	include_once($this->store->get_config("code")."modules/mod_yui.php");

	$grants = array();
	$userName = "<strong>'No user selected'</strong>";

	$userPath = $this->getvar('selecteduser');
	if ( $userPath && $this->exists($userPath) ) {
		$user = current($this->get($userPath, 'system.get.phtml'));
		if ( $user->implements( 'puser' ) ) {
			$userName = $user->data->name;
			if (is_array($user->data->config->usergrants??null)) {
					foreach ($user->data->config->usergrants as $grantsPath => $grantsArray) {
						if (sizeof($grantsArray) && $this->exists($grantsPath)) {
							$object = current($this->get($grantsPath, 'system.get.phtml'));
							$grants[$grantsPath] = array(
								'name'		=> $object->nlsdata->name,
								'type'		=> $object->type,
								'grants'	=> array(
									'array'		=> $grantsArray,
									'string'	=> grantsArrayToString($grantsArray)
								)
							);
						}
					}
			}
		}

	}

/*	$other_grants = array(
		"/sites/mysite/" => array(
			"name" => "My site",
			"type" => "psite",
			"grants" => array(
				"string" => "add edit read"
			)
		),
		"/sites/mysite/two/" => array(
			"name" => "Section two",
			"type" => "psection",
			"grants" => array(
				"string" => "read"
			)
		),
		"/sites/mysite/two/contact/" => array(
			"name" => "Contact",
			"type" => "pdir",
			"grants" => array(
				"string" => "add edit read"
			)
		)
	);
*/
?>
	<div class="other_grants"><h2><?php echo $ARnls['ariadne:grants:other_grants']; echo $userName; ?></h2>
		<?php 	foreach ($grants as $path => $info) { ?>
				<div class="item" title="<?php echo htmlspecialchars($info['name']??'');?>">
					<img src="<?php echo $this->call('system.get.icon.php', array('type' => $info['type'], 'size' => 'medium')); ?>" alt="<?php echo $info['type'];?>">
					<div class="info">
						<span class="path"><?php echo yui::labelspan($path, 30); ?></span><br>
						<span class="grants"><?php echo $info['grants']['string']; ?></span>
					</div>
				</div>
		<?php	} ?>
	</div>
