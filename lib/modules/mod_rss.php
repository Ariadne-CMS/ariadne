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
			$context = pobject::getContext();
			$me = $context['arCurrentObject'];
			$rss = new rssFeed($me);
			$rss->setFeedUrl($url, $username, $password);
			return $rss;
		}

		function loadFromString($string) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			/* parse rss feed and initialize and return an rssFeed object */
			$rss = new rssFeed($me);
			$rss->setFeedString($string);
			return $rss;
		}

	}

	class rssFeed {

		function __construct($object) {
			$this->object = $object;
		}

		function setFeedUrl($url, $username='', $password='') {
			if (preg_match('|^https?://|i', $url)) {
				$this->rss_url = $url;
				$this->rss_user = $username;
				$this->rss_password = $password;
				$this->reset();
			} else {
				$this->error = "$url is not a valid URL";
			}
		}

		function setFeedString($feed) {
			$this->feedstring = $feed;
			$this->reset();
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

		function _ls($template, $args='', $limit=100, $offset=0) {
			return $this->ls($template, $args, $limit, $offset);
		}

		function _getArray($limit=100, $offset=0) {
			return $this->getArray($limit, $offset);
		}

		function reset() {
			// reset namestack
			$this->ns = Array();
			$this->elements = Array();
			$this->rss_items = Array();


			if ($this->rss_fp) {
				fclose($this->rss_fp);
			}
			if ($this->rss_url) {
				if (!preg_match('|^https?://|i', $this->rss_url)) {
					$this->error = $this->rss_url." is not a valid URL";
				} else {
					$this->rss_fp = fopen($this->rss_url, "r");
					if (!$this->rss_fp) {
						$this->error = "Could not open RSS ".$this->rss_url;
					}
				}
			}

			// Finding the RSS feed source encoding - thanks to the pointers on
			// http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
			//
			// Read the first part of the RSS feed to find the encoding.


			// FIXME: We kunnen niet terugseeken dus dit werkt niet.
			if ($this->rss_fp) {
				$this->xmldata = fread($this->rss_fp, 4096);
			} else if ($this->feedstring) {
				$this->xmldata = $this->feedstring;
			}
			// Prepare a regexp to find the source encoding, and match it. If we find it, use that - otherwise assume UTF-8 as the default XML encoding.
			$encoding_regexp = '/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m';

//			$encoding_regexp = '/.*encoding=[\'"]/m';

			if (preg_match($encoding_regexp, $this->xmldata, $matches)) {
				$this->encoding = strtoupper($matches[1]);
			} else {
				$this->encoding = "UTF-8";
			}

			// The RSS library only understands UTF-8, US-ASCII and ISO-8859-1, so only give it this. For other encodings we'll default to UTF-8.

			if($this->encoding == "UTF-8" || $this->encoding == "US-ASCII" || $this->encoding == "ISO-8859-1") {
				$this->parser = xml_parser_create($this->encoding);
			} else {

				$this->parser = xml_parser_create("UTF-8");
			}


			//$this->parser = xml_parser_create();
			xml_set_object($this->parser, $this);
			xml_set_element_handler($this->parser, "startElement", "endElement");
			xml_set_character_data_handler($this->parser, "characterData");
			xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
			xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, "UTF-8");

		}

		function startElement($parser, $name, $attribs) {
			$newElement = Array();
			$element = &$this->elements;
			foreach ($this->ns as $n) {
				$element = &$element[$n];
			}
			$this->ns[] = $name;
			switch ($name) {
				default:
					$element[$name] = $newElement;
					if ($attribs) {
						foreach($attribs as $attribName => $attribValue ) {
							$element[$name.':'.$attribName] = $attribValue;
						}
					}
				break;
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
				case 'rdf:RDF':
				case 'rdf:Seq':
				case 'rdf:li':
				case 'items':

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
			if (!$this->parser) {
				return false;
			}

			xml_set_object($this->parser, $this);

			/* remove the last item from the queue */
			if (count($this->rss_items)) {
				array_shift($this->rss_items);
			}
			if (!count($this->rss_items) && ($this->xmldata || ($this->rss_fp && !feof($this->rss_fp)))) {
				do {
					// The first read has already been done in the reset() function!
					if ($this->xmldata) {
						$rss_data = $this->xmldata;
						$this->xmldata = false;
						if (!($this->rss_fp)) {
							$eof = true;
						}
					} else if ($this->rss_fp) {
						$rss_data = fread($this->rss_fp, 4096);
						$eof = feof($this->rss_fp);
					}

/*
					if(function_exists('mb_convert_encoding')) {
						$encoded_source = @mb_convert_encoding($rss_data, "UTF-8", $this->encoding);
					}
					if($encoded_source != NULL) {
						$rss_data = str_replace ( $this->xml_enc,'<?xml version="1.0" encoding="utf-8"?>', $encoded_source);
					}
*/
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
			if (!$current) { // feed is either not yet initialized or ended, in both cases the following line has the correct result
				$current = $this->next();
			}
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