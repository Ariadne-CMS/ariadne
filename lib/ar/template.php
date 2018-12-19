<?php

	class ar_template extends arBase {
		private $cache = [];
		private $compiledCache = [];

		private function getStorageLayer($path) {
			global $AR;
			if (isset($AR->templateStore)) {
				$layers = array_keys($AR->templateStore);
				// fallback
				$layer = array_reduce($layers, function($carry, $item) use ($path) {
					if(strpos($path, $item) === 0) {
						// item is a prefix of item
						if(!isset($carry) || (strlen($carry) < strlen($item) )) {
							// item is more specific
							$carry = $item;
						}
					}
					return $carry;
				}, null);
				if(isset($layer)) {
					$config =  $AR->templateStore[$layer];
					$classname = 'ar_template_' . $config['driver'];
					return new $classname($layer, $config);
				}
			}
			return new ar_template_filestore('/',[]);
		}

		public function get($path, $name){
			if(!isset($this->compiledCache[$path][$name])) {
				$this->compiledCache[$path][$name] = self::getStorageLayer($path)->get($path, $name);
			}
			return $this->compiledCache[$path][$name];
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
