<?php

	class ar_template extends arBase {
		private $cache = [];

		private function getStorageLayer($path) {
			return new ar_template_filestore($path);
		}

		public function get($path, $name){
			return self::getStorageLayer($path)->get($path, $name);
		}

		public function save($path, $name, $template, $local=null, $private=null) {
			return self::getStorageLayer($path)->save($path, $name, $template, $local, $private);
		}

		public function load($path, $name) {
			return self::getStorageLayer($path)->load($path, $name);
		}

		public function ls($path) {
			if (!isset($this->cache[$path])){
				$this->cache[$path] = self::getStorageLayer($path)->ls($path);
			}
			return $this->cache[$path];
		}

		public function rm($path, $name){
			return self::getStorageLayer($path)->rm($path, $name);
		}

		public function exists($path, $name) {
			return self::getStorageLayer($path)->exists($path, $name);
		}

		public function compile($path, $name) {
			return self::getStorageLayer($path)->compile($path, $name);
		}
	}
