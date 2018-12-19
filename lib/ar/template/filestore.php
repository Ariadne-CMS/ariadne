<?php

	class ar_template_filestore extends arBase {

		private function getFilestore() {
			$context = ar::context()->getObject();
			if(isset($context)) {
				$templates = $context->store->get_filestore("templates");
			} else {
				global $store;
				$templates = $store->get_filestore("templates");
			}
			return $templates;
		}

		private function pathToId($path) {
			$context = ar::context()->getObject();

			if($context->path == $path) {
				$result = $context->id;
			} else {
				$result = $context->loadConfig($path)->id;
			}
			return $result;
		}

		private function getConfig($path) {
			$context = ar::context()->getObject();
			return $context->loadConfig($path);
		}

		public function get($path, $name){
			$fs = $this->getfilestore();
			$id = $this->pathtoid($path);

			return (
				$fs->import($id, $name) 
			);
		}

		public function save($path, $name, $template, $local=null, $private=null) {
			return false;
		}

		public function load($path, $name) {
			$fs = $this->getfilestore();
			$id = $this->pathtoid($path);

			return $fs->read($id, $name . '.pinp');
		}

		public function ls($path) {
			$result = [];
			$fs = $this->getFilestore();
			$id = $this->pathToId($path);

			$config = $this->getConfig($path);

			$templates = $config->pinpTemplates;
			if (isset($templates)) foreach($templates as $type => $names) {
				if (isset($names)) foreach($names as $name => $languages) {
					if (isset($languages)) foreach($languages as $language => $id ) {
						$tempname = sprintf("%s.%s.%s",$type,$name,$language);
						list($maintype,$subtype) = explode('.', $type, 2);
						if(!isset($result[$name])) {
							$result[$name] = [];
						}
						$result[$name][] = [
							'id'       => $config->id,
							'path'     => $path,
							'type'     => $maintype,
							'subtype'  => $subtype,
							'name'     => $name,
							'filename' => $tempname,
							'language' => $language,
							'private'  => isset($config->privatetemplates[$type][$name]),
							'local'    => !isset($config->localTemplates[$type][$name][$language]),
						];
					}
				}
			}
			return $result;

		}

		public function rm($path, $name){
			$fs = $this->getfilestore();
			$id = $this->pathtoid($path);

			return (
				$fs->remove($id, $name) 
			);
		}

		public function exists($path, $name) {
			$fs = $this->getFilestore();
			$id = $this->pathToId($path);

			return (
				$fs->exists($id, $name . '.inc') 
			);
		}

		public function compile($path, $name) {
			// FIXME
		}
	}
