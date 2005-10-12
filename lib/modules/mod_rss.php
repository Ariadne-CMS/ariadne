<?php
	/*
	
	*/

	class pinp_rss {

		function _loadFromUrl($url, $username='', $password='') {
		/* Loads an rss feed from a url */
			return rss::loadFromUrl($url, $username, $password);
		}

		function _loadFromString($rss) {
			return rss::loadFromString($rss);
		}

	}

	class rss {

		function loadFromUrl($url, $username='', $password='') {
			/* Loads an rss feed from a url */
			$rss = new rssFeed($this);
			$rss->setFeedUrl($url, $username, $password);
			return $rss;
		}

		function loadFromString($rss) {
		/* parse rss feed and initialize and return an rssFeed object */
		}

	}

	class rssFeed {

		function rssFeed(&$object) {
			$this->object = $object;
		}

		function setFeedUrl($url, $username='', $password='') {
			if (eregi('^https?://', $url)) {
				$this->rss_url = $url;
				$this->rss_user = $username;
				$this->rss_password = $password;
				$this->reset();
			} else {
				$this->error = "$url is not a valid URL";
			}
		}

		function setFeedString($feed) {

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
		
		function reset() {
			// reset namestack
			$this->ns = Array();
			$this->elements = Array();
			$this->rss_items = Array();
			$this->parser = xml_parser_create();

			xml_set_object($this->parser, $this);
			xml_set_element_handler($this->parser, "startElement", "endElement");
			xml_set_character_data_handler($this->parser, "characterData");
			xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);

			if ($this->rss_fp) {
				fclose($this->rss_fp);
			}
			if ($this->rss_url) {
				if (!eregi('^https?://', $this->rss_url)) {
					$this->error = $this->rss_url." is not a valid URL";
				} else {
					$this->rss_fp = fopen($this->rss_url, "r");
					if (!$this->rss_fp) {
						$this->error = "Could not open RSS ".$this->rss_url;
					}
				}
			}
		}

		function startElement($parser, $name, $attribs) {
			$newElement = Array();
			$element = &$this->elements;
			foreach ($this->ns as $n) {
				$parentElement = $element;
				$element = &$element[$n];
			}
			$this->ns[] = $name;
			switch ($name) {
				default:
					$element[$name] = $newElement;
			}
		}

		function endElement($parser, $name) {
			$element = &$this->elements;
			foreach ($this->ns as $n) {
				$parentElement = $element;
				$element = &$element[$n];
			}
			switch ($name) {
				case 'item':
					$this->rss_items[] = $element;
					unset($parentElement[$name]);
				break;
			}
			array_pop($this->ns);
		}

		function characterData($parser, $data) {
			$element = &$this->elements;
			foreach ($this->ns as $n) {
				$element = &$element[$n];
			}
			switch ($n) {
				case 'item':
				case 'rss':
				case 'channel':
				case 'image':
				break;
				default:
					if (!$element) {
						$element = "";
					}
					$element .= $data;
				break;
			}
		}


		function next() {
			// this is needed 
			xml_set_object($this->parser, $this);

			$eof = true;
			/* remove the last item from the queue */
			if (count($this->rss_items)) {
				array_shift($this->rss_items);
			}
			if (!count($this->rss_items) && $this->rss_fp && !feof($this->rss_fp)) {
				do {
					$rss_data = fread($this->rss_fp, 4096);
					$eof = feof($this->rss_fp);
					if (!xml_parse($this->parser, $rss_data, $eof)) {
						$this->error = sprintf("XML error: %s at line %d",
							xml_error_string(xml_get_error_code($this->parser)),
								xml_get_current_line_number($this->parser));
					}
				} while (!$this->error && !$eof && !count($this->rss_items));
			}
			return $this->rss_items[0];
		}

		function current() {
			return $this->rss_items[0];
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
			while ($this->next()) { $i++; };
			return $i;
		}

		function ls($template, $args='', $limit=100, $offset=0) {
		global $ARBeenHere;
			$ARBeenHere = Array();
			$this->reset();
			if ($offset) {
				while ($offset) {
					$this->next();
					$offset--;
				}
			}
			do {
				$ARBeenHere = Array();
				$this->call($template, $args);
				$limit--;
			} while ($this->next() && $limit);
		}

		function getArray($limit=100, $offset=0) {
			$result=Array();
			$this->reset();
			if ($offset) {
				while ($offset) {
					$this->next();
					$offset--;
				}
			}
			do {
				$result[]=$this->current();
				$limit--;
			} while ($this->next() && $limit);
			return $result;
		}

	}

?>