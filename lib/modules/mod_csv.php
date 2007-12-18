<?php

	class pinp_csv {

		function _init($settings = "") {
			return csv::init($settings);
		}

		function _load($fileName = "file", $fileNameNls = "", $settings = "") {
			return csv::load($fileName, $filenameNls, $settings);
		}

	}

	class csv {

		function init($settings = "") {
			return new csvFeed($this, $settings);
		}

		function load($fileName = "file", $fileNameNls = "", $settings = "") {
			$csv = new csvFeed($this, $settings);
			$csv->load($fileName, $fileNameNls);
			return $csv;
		}

	}

	class csvFeed {

		function csvFeed(&$object, $settings) {
			$default = Array(
				"seperator"		=> ",",
				"quotation"		=> "\"",
				"charset"		=> "utf-8",
				"keyRow"		=> null,
				"keySelection"	=> null,
				"bufferLength"	=> 4096 * 4
			);
			if (!$settings) {
				$settings = Array();
			}
			foreach ($default as $key => $value) {
				if (!isset($settings[$key]) || $settings[$key] === "" ) {
					$settings[$key] = $value;
				}
			}
			if (!isset($settings["escape"])) {
				$settings["escape"] = $settings["quotation"];
			}
			$this->config = $settings;
			$this->object = $object;
			$this->readMode = false;
		}

		function load($fileName = "file", $fileNameNls = "") {
			$object = &$this->object;

			$files	= $object->store->get_filestore("files");
			if (!$fileName) {
				$fileName = "file";
			}
			if (!$fileNameNls) {
				$fileNameNls = $object->reqnls;
			}
			if ($files->exists($object->id, $fileNameNls."_$fileName")) {
				$fileName = $fileNameNls."_$fileName";
			}
			$tempDir	= $object->store->get_config("files")."temp/";
			$tempFile	= tempnam($tempDir, "csvexport");
			$files->copy_from_store($tempFile, $object->id, $fileName);

			$this->readMode = "fp";
			$this->fp = fopen($tempFile, "r");
			$this->reset();
		}

		function _load($fileName = "file", $fileNameNls = "") {
			return $this->load($fileName, $fileNameNls);
		}
		
		function reset() {
			switch ($this->readMode) {
				default:
				case "fp":
					fseek($this->fp, 0);
					if (isset($this->config['keyRow']) && $this->config['keyRow'] !== "") { // csv lib saves defaults as ""
						$this->keys = array();
						for ($i = 0; $i <= $this->config['keyRow']; $i++) {
							$keys = $this->next();
						}
						$this->keys = $keys;
						if (!$this->config['keySelection']) {
							$this->config['keySelection'] = $keys;
						}
					}
					$this->readLine = "";
				break;
			}
			$this->next(); // set pointer to first item for current()
		}


		function next() {
			switch ($this->readMode) {
				default:
				case "fp":
					if (feof($this->fp)) {
						$result = Array();
					} else {
						$result = fgetcsv($this->fp, $this->config['bufferLength'], $this->config['seperator'], $this->config['quotation']);
						if (strtolower($this->config['charset']) != "utf-8") {
							if (!function_exists("iconv")) {
								global $store;
								include_once($store->get_config("code")."modules/mod_unicode.php");
								foreach ($result as $item => $resultItem) {
									$result[$item] = unicode::convertToUTF8($this->config["charset"], $result[$item]);
								}
							} else {
								foreach ($result as $item => $resultItem) {
									$result[$item] = iconv($this->config["charset"], "utf-8", $result[$item]);
								}
							}
						}
					}
				break;
			}
			if ($result && $this->keys && $this->config['keySelection']) {
				$hashResult = Array();
				foreach ($this->keys as $i => $key) {
					if (in_array($key, $this->config['keySelection'])) {
						$hashResult[$key] = $result[$i];
					}
				}
				$result = $hashResult;
			}
			$this->readLine = $result;
			return $result;
		}


		function current() {
			return $this->readLine;
		}

		function call($template, $args=Array()) {
			$current = $this->current();
			if ($current) {
				$args['item'] = $current;
				$result = $this->object->call($template, $args);
			}
			return $result;
		}

		function count() {
			$this->reset();
			$i = 0;
			while ($this->current()) { $i++; $this->next(); };
			return $i;
		}

		function ls($template, $args='', $limit=0, $offset=0) {
		global $ARBeenHere;
			$ARBeenHere = Array();
			$this->reset();
			if ($offset) {
				while ($offset) {
					$this->next();
					$offset--;
				}
			}
			if( $limit == 0 ) { $limit = -1; }
			while($this->current() && $limit ) {
				$ARBeenHere = Array();
				$args["item"] = $this->current();
				$this->call($template, $args);
				$limit--;
				$this->next();
			} 
		}

		function _getArray($limit=0, $offset=0) {
			return $this->getArray($limit,$offset);
		}

		function getArray($limit=0, $offset=0) {
			$result=Array();
			$this->reset();
			if ($offset) {
				while ($offset) {
					$this->next();
					$offset--;
				}
			}
			if( $limit == 0 ) { $limit = -1; }
			while( $this->current() && $limit ) {
				$result[]=$this->current();
				$limit--;
				$this->next();
			}
			return $result;
		}

		function _reset() {
			return $this->reset();
		}

		function _next() {
			return $this->next();
		}

		function _count() {
			return $this->count();
		}

		function _current() {
			return $this->current();
		}

		function _ls($template, $args='') {
			return $this->ls($template, $args);
		}

	}

?>