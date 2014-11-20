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
				$this->cachestore->save( '/', 'pobject', new object );
			}

			$data = new object;
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
			$query = "template.value='$id:$type:$name'";

			$objects = $this->cachestore->find("/", $query, 0, 0);

			$result = $this->cachestore->call("system.get.filename.phtml","",$objects);
			$result = array_unique($result);

			foreach ($result as $filename) {
				$this->invalidate($filename);
			}
		}

		public function onObjectSaved($id) {
			$query = "objectref.value='$id'";
			$objects = $this->cachestore->find("/", $query, 0, 0);

			$result = $this->cachestore->call("system.get.filename.phtml","",$objects);
			$result = array_unique($result);

			foreach ($result as $filename) {
				$this->invalidate($filename);
			}
		}

		public function invalidate($filename) {
			global $store;
			$absFilename = $store->get_config("files")."cache/".$filename;

			if (file_exists($absFilename)) {
				if (filemtime($absFilename) > time()) {
					touch($absFilename, time() + 1); // set mtime to now; this means the cache image is now invalid;
				}
			}
		}

		public function delete($filename) {
			$this->cachestore->purge("/" . md5($filename) . "/");
		}
	}
?>
