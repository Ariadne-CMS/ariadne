<?php

class import_wddx {

	var $stack;
	var $nestdeep;
	var $input;
	var $xml_parser;

	function import_wddx($input){
		$this->input = $input;
		$this->nestdeep = -4;
		$this->stack = array();
		$this->xml_parser = xml_parser_create();
		// use case-folding so we are sure to find the tag in $map_array
		xml_set_object($this->xml_parser, &$this);
		xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($this->xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($this->xml_parser, "characterData");
	}

	function startElement($parser, $name, $attribs) {
		$this->nestdeep++;
		switch($name) {
			case "wddxPacket":
			case "header":
			case "comment":
			case "version":
			case "data":
				array_push($this->stack, Array("type" => "top", "data" => Array()));
			break;
			case "struct":
				$value = Array();
				array_push($this->stack, Array("type" => $attribs["type"], "data" => $value));
			break;
			case "var":
				array_push($this->stack, Array("type" => $name, "data" => $attribs["name"]));
			break;
			case "number":
			case "string":
			case "boolean":
				array_push($this->stack, Array("type" => $name));
			break;
		}
	}
	
	function endElement($parser, $name) {
		$this->nestdeep--;

		$element = &$this->stack[count($this->stack)-1];
		$element["locked"] = true;

		switch($name) {
			case "var":
				$value = array_pop($this->stack);
				if ($value["type"] == "var") {
					/* we did get a key without a value */
					$var = $value;
				} else {
					$var = array_pop($this->stack);
				}
				$struct = array_pop($this->stack);

				$key = $var["data"];
				$struct["data"][$key] = $value["data"];
				array_push($this->stack, $struct);
			break;
		}
		if($this->nestdeep == 0){
				$object = array_pop($this->stack);
				if(is_array($object) && is_array($object["data"])){
					echo "#\n";
					print_r($object["data"]);
					echo "#\n";
				}
		}
		
	}
	
	function characterData($parser, $data) {
		$element = &$this->stack[count($this->stack)-1];
		if (!$element["locked"]) {
			switch ($element["type"]) {
				case "number":
					$element["data"] = (int)$data;
				break;
				case "boolean":
					switch ($data) {
						case "true":
							$element["data"] = true;
						break;
						default:
							$element["data"] = false;
					}
				break;
				case "string":
					$element["data"] .= $data;
				break;
			}
		}
	}
	
	function parse() {
		while ($data = fgets($this->input, 65535)) {
				if (!xml_parse($this->xml_parser, $data, feof($this->input))) {
					die(sprintf("XML error: %s at line %d",
								xml_error_string(xml_get_error_code($this->xml_parser)),
								xml_get_current_line_number($this->xml_parser)));
				}
		}
		xml_parser_free($this->xml_parser);
	}
}
?>