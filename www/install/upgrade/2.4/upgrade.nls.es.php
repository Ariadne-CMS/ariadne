<?php
		$offset = 0;
		do {
			$result = $store->call('system.get.phtml', '', $store->find('/', '', 100, $offset));
			foreach ($result as $object) {
				$changed = false;
				if (isset($object->data->nls->list['es'])) {
					$changed = true;
					$object->data->nls->list['es'] = $AR->nls->list['es'];
				}

				if ($changed) {
					echo "updating: ".$object->path."<br>\n";
					$store->save($object->path, $object->type, $object->data);
					if($store->error){
						echo "Error: ".$store->error;
						$error .= $store->error;
					}
				}
			}
			$offset += 100;
		} while (count($result) >= 100);

		echo "Done updating ariadne nls names<br>\n";
?>
