<?php
	class cache {
		public function __construct($cache_config) {
			debug("cache([array])","store");
			// init cache store
			$inst_store = $cache_config["dbms"]."store";
			include_once($cache_config["code"]."stores/$inst_store.phtml");
			$this->cachestore=new $inst_store($cache_config["root"], $cache_config);
		}

		public function save($filename, $objectChain, $templateChain) {
			if (!is_array($objectChain)) {
				return false;
			}
			if (!is_array($templateChain)) {
				return false;
			}
			if ( !$this->cachestore->exists('/') ) {
				$this->cachestore->save( '/', 'pobject', new baseObject );
			}

			$data = new baseObject;
			$data->filename = $filename;
			$data->objectChain = $objectChain;
			$data->templateChain = $templateChain;

                        $properties = array();
			$properties["objectref"] = array();
			$properties["template"] = array();

			foreach ($objectChain as $id => $value) {
				$properties["objectref"][] = array("name" => "id", "value" => $id);
			}
			foreach ($templateChain as $id => $template) {
				foreach ($template as $name => $value) {
					foreach ($value as $type => $dummy) {
						$properties["template"][] = array("name" => "name", "value" => $id . ":" . $type . ":" . $name);
					}
				}
			}

			$this->cachestore->save("/" . md5($filename) . "/", "pcache", $data, $properties);

			// $onsave = $this->onTemplateSaved("1944", "ppage", "ppage.view.div1.html.any");
		}

		public function onTemplateSaved($id, $type, $name) {
			$query = "template.value='$id:$type:$name' order by none";
			$objects = $this->cachestore->find("/", $query, 0, 0);

			$template = function($object) {
				return $object->data->filename;
			};

			$result = $this->cachestore->call($template,array(),$objects);
			$result = array_unique($result);

			foreach ($result as $filename) {
				$this->invalidate($filename);
			}
		}

		public function onObjectSaved($id) {
			$query = "objectref.value='$id' order by none";
			$objects = $this->cachestore->find("/", $query, 0, 0);

			$template = function($object) {
				return $object->data->filename;
			};

			$result = $this->cachestore->call($template,array(),$objects);
			$result = array_unique($result);

			foreach ($result as $filename) {
				$this->invalidate($filename);
			}
		}

		public function invalidate($filename) {
			global $store;
			$absFilename = $store->get_config("files")."cache/".$filename;
			$stamp = time();

			if (file_exists($absFilename)) {
				if (filemtime($absFilename) > $stamp + 2) {  // do not touch file which will expire soon
					touch($absFilename, $stamp + 1); // set mtime to now; this means the cache image is now invalid;
				}
			}
		}

		public function delete($filename) {
			$this->cachestore->delete("/" . md5($filename) . "/");
		}
	}
?>
