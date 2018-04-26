<?php
		$offset = 0;
		do {
			$result = $store->call('system.get.phtml', '', $store->find('/', '', 100, $offset));
			foreach ($result as $object) {
				$changed = false;
				if (!$object->data->config) {
					$config = new baseObject;
				} else {
					$config = $object->data->config;
				}

				if ($object->data->templates) {
					$config->templates = $object->data->templates;
					unset($object->data->templates);
					$changed = true;
				}
				if ($object->data->pinp) {
					$config->pinp = $object->data->pinp;
					unset($object->data->pinp);
					$changed = true;
				}
				if ($object->data->grants) {
					$config->grants = $object->data->grants;
					unset($object->data->grants);
					$changed = true;
				}
				if ($object->data->usergrants) {
					$config->usergrants = $object->data->usergrants;
					unset($object->data->usergrants);
					$changed = true;
				}
				if ($object->data->cacheconfig) {
					$config->cacheconfig = $object->data->cacheconfig;
					unset($object->data->cacheconfig);
					$changed = true;
				}
				if ($object->data->typetree) {
					$config->typetree = $object->data->typetree;
					unset($object->data->typetree);
					$changed = true;
				}
				if ($object->data->nlsconfig) {
					$config->nlsconfig = $object->data->nlsconfig;
					unset($object->data->nlsconfig);
					$changed = true;
				}
				if ($object->data->customconfig) {
					$config->customconfig = $object->data->customconfig;
					unset($object->data->customconfig);
					$changed = true;
				}
				if ($object->data->owner) {
					$config->owner = $object->data->owner;
					$config->owner_name = $object->data->owner_name;
					unset($object->data->owner);
					unset($object->data->owner_name);
					$changed = true;
				}

				if ($changed) {
					$object->data->config = $config;
					echo "updating: ".$object->path."<br>\n";
					$store->save($object->path, $object->type, $object->data);
				}
			}
			$offset += 100;
		} while (count($result) >= 100);

		echo "Done updating ariadne configuration data<br>\n";
?>
