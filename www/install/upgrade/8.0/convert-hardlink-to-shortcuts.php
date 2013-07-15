<?php

	function convertUserToShortcut($object) {
		global $store;

		$paths = $store->list_paths($object->path);
		if( count($paths) > 1 ) {
			$realpath = null;
			foreach($paths as $key => $p ) {
				if( preg_match('|^/system/users/|', $p) ) {
					$realpath = $p;
					unset($paths[$key]);
					break;
				}
			}

			if( $realpath ) {
				foreach ($paths as $path ) {
					echo $path .":". $realpath ."\n";

					$store->delete($path);
					$mygroup = current($object->get($object->make_path($path.'/../'),"system.get.phtml"));
					$config = $mygroup->loadConfig();
					$input = Array(
							$config->nls->default  => Array(
								'name' => $object->data->name
								),
							'arNewType'     => 'pshortcut',
							'arNewFilename' => basename($path),
							'keepurl'       => true,
							'path'          => $realpath
							);

					$mygroup->call('system.new.phtml', $input );
				}
			}
		}
	}

	$offset = 0;
	do {
		$result = $store->call('system.get.phtml', '', $store->find('/system/groups/', 'object.type =  "puser"', 100, $offset));
		foreach ($result as $object) {
			convertUserToShortcut($object);
		}
		$offset += 100;
	} while (count($result) >= 100);

	echo "Done updating user hardlinks<br>\n";
?>