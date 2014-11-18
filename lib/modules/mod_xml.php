<?php
	class pinp_xml {
		function _parser() {
			$context = pobject::getContext();
			$me      = $context['arCurrentObject'];
			$parser = new xml_parser($me);
			return $parser;
		}

		function _escape($text) {
			$search	= Array('&','"',"'",'<','>');
			$replace= Array('&amp;','&quot;','&apos;','&lt;','&gt;');
			return str_replace($search, $replace, $text);
		}

		function _unescape($text) {
			$search= Array('&quot;','&apos;','&lt;','&gt;','&amp;');
			$replace= Array('"',"'",'<','>','&');
			return str_replace($search, $replace, $text);
		}

	};

	class xml_parser {

		function xml_parser(&$object) {
			$this->object = $object;
		}

		function _set_element_handler($tag_open, $tag_close) {
			$this->tag_open_template = $tag_open;
			$this->tag_close_template = $tag_close;
		}

		function _set_character_data_handler($tag_data) {
			$this->tag_data_template = $tag_data;
		}

		function _parse($string) {
			$parser = xml_parser_create();
			xml_set_object($parser, $this);
			xml_set_element_handler($parser, "call_tag_open", "call_tag_close");
			xml_set_character_data_handler($parser, "call_tag_data");
			if (!xml_parse($parser, $string)) {
				$this->error = sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($parser)),
						xml_get_current_line_number($parser));
			}
		}

		function _get_array($string, $MULTI_TAGS = Array()) {
			$parser = xml_parser_create();
            $this->elements = Array();
			$this->MULTI_TAGS = Array();
			foreach ($MULTI_TAGS as $tag) {
				$this->MULTI_TAGS[] = strtoupper($tag);
			}
			xml_set_object($parser, $this);
			xml_set_element_handler($parser, "startElement", "endElement");
			xml_set_character_data_handler($parser, "characterData");
			if (!xml_parse($parser, $string)) {
				$this->error = sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($parser)),
						xml_get_current_line_number($parser));
			}

			return $this->elements;
		}

		function _parse_url($url) {
			if (!preg_match('|^https?://|i', $url)) {
				$this->error = "Not a valid URL ($url)";
			} else {
				$parser = xml_parser_create();
				xml_set_object($parser, $this);
				xml_set_element_handler($parser, "call_tag_open", "call_tag_close");
				xml_set_character_data_handler($parser, "call_tag_data");
				$fp = fopen($url, "r");
				if (!$fp) {
					$this->error = "Could not open ($url)";
				} else {
					while (!$this->error && !feof($fp)) {
						$string = fread($fp, 4096);
						if (!xml_parse($parser, $string)) {
							$this->error = sprintf("XML error: %s at line %d",
								xml_error_string(xml_get_error_code($parser)),
									xml_get_current_line_number($parser));
						}
					}
					fclose($fp);
				}
			}
		}

		function _parse_curl($url) {
			if (!preg_match('|^https?://|i', $url)) {
				$this->error = "Not a valid URL ($url)";
			} else {
				$parser = xml_parser_create();
				xml_set_object($parser, $this);
				xml_set_element_handler($parser, "call_tag_open", "call_tag_close");
				xml_set_character_data_handler($parser, "call_tag_data");

				$ch = curl_init($url);

				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

				$string = curl_exec($ch);
				curl_close($ch);
				if (!xml_parse($parser, $string)) {
					$this->error = sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($parser)),
					xml_get_current_line_number($parser));
				}

			}
		}

		function call_tag_open($parser, $tag, $attributes) {
			global $ARBeenHere;
			$ARBeenHere = Array();
			if ($this->tag_open_template) {
				$this->object->call($this->tag_open_template, Array("tag" => $tag, "attributes" => $attributes));
			}
		}

		function call_tag_close($parser, $tag) {
			global $ARBeenHere;
			$ARBeenHere = Array();
			if ($this->tag_close_template) {
				$this->object->call($this->tag_close_template, Array("tag" => $tag));
			}
		}

		function call_tag_data($parser, $data) {
			global $ARBeenHere;
			$ARBeenHere = Array();
			if ($this->tag_data_template) {
				$this->object->call($this->tag_data_template, Array("tag_data" => $data));
			}
		}


		function startElement($parser, $name, $attribs) {
		//global $MULTI_TAGS;
			$newElement = Array();
			$element = &$this->elements;

			if (is_array($this->ns)) {
				foreach ($this->ns as $n) {
					$element = &$element[$n];
				}
			}

			$this->ns[] = $name;
			$newElement[':attribs'] = $attribs;
			if (!in_array($name, $this->MULTI_TAGS)) {
				$element[$name] = $newElement;
			} else {
				$element[$name][] = $newElement;
				$this->ns[] = sizeof($element[$name])-1;
			}
		}

		function endElement($parser, $name) {
		//global $MULTI_TAGS;
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
			$parent = $this->ns[sizeof($this->ns)-2];
			if (in_array($parent, $this->MULTI_TAGS) && $parent === $name) {
				array_pop($this->ns);
			}
			array_pop($this->ns);
		}

		function characterData($parser, $data) {
			$element = &$this->elements;
			$name = "";
			foreach ($this->ns as $n) {
				$name .= ":$n";
				$element = &$element[$n];
			}
			switch ($n) {
				// do not put anything else above this line
				// or else '0' values will trigger it.
				case 0:
				default:
					if (!$element) {
						$element = Array();
					}
					$element[':data'] .= $data;
				break;
			}
		}
	}
