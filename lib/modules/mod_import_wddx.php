<?php
class import_wddx {

	var $stack;
	var $nestdeep;
	var $input;
	var $xml_parser;
	var $store;
	var $config;

	function import_wddx(){
		$this->input = null;
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
				array_push($this->stack, Array("type" => "top", "data" => array()));
				break;
			case "struct":
				if(($attribs["class"] == "object" || $attribs["class"] == "stdClass") && ($attribs["type"] == "object" ))
				{
					$value = new object();
				} else
				{
					$value = Array();
				}
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

		if($name == "var")
		{
			$value = array_pop($this->stack);
			if ($value["type"] == "var") {
				/* we did get a key without a value */
				$var = $value;
			} else {
				$var = array_pop($this->stack);
			}
			$struct = array_pop($this->stack);

			$key = $var["data"];
			if(is_object($struct["data"]))
			{
				$struct["data"]->$key = $value["data"];
			} else if(is_array($struct["data"]))
			{
				$struct["data"][$key] = $value["data"];
			} else {
				echo "what the fuck is this\n";
				echo "#.#.#.#\n";
				print_r($struct["data"]);
				echo "#.#.#.#\n";
				print_r($stack);
				echo "#.#.#.#\n";
			}

			array_push($this->stack, $struct);
		}
		if($this->nestdeep == 0){
			$object = array_pop($this->stack);
			if(is_array($object) && is_array($object["data"])){
				$this->saveObject($object["data"]);
			}
		}

	}

	function saveObject(&$objdata){
		/*
			1) object data
			2) object templates
			3) object grants
			4) object files
		 */
		//debugon('all');
		debug("working on ".$objdata['path'],'all');
		$path = $objdata['path'];
		//$path = '/test'.$path;

		/*
			step 1
			if not skip data
			load object if exists
			copy data into object
			else
			create new object
			fi
			save
			fi
		 */
		if(!($this->config['skipdata'] === true))
		{
			debug('work data','all');
			if($id = $this->store->exists($path))
			{
				debug("data: object exists",'all');
				$object = current(
						$this->store->call("system.get.phtml",
							array(),$this->store->get($path))
						);
				if(
						($object->lastchanged <= $objdata['lastchanged']) ||
						($this->config['forcedata'] === true))
				{
					$tmpconfig = $object->data->config;
					unset($object->data);
					$object->data = $objdata['data'];
					$object->data->config->grants = $tmpconfig->grants;
					$object->data->config->pinp = $tmpconfig->pinp;
					$object->data->config->templates = $tmpconfig->templates;
					$object->type = $objdata['type'];
					$object->vtype = $objdata['vtype'];
					$object->lastchanged = $objdata['lastchanged'];
					$object->size = $objdata['size'];
					$object->priority = $objdata['priority'];
					$object->type = $objdata['type'];
					debug("data: calling save");
					$object->save($objdata['properties']);
				}
			} else
			{
				debug("data: object doesn't exists",'all');
				$object = $this->store->newobject($path,
						$this->store->make_path($path,'..'), $objdata['type'],
						$objdata['data'], 0, $objdata['lastchanged'],
						$objdata['vtype'], $objdata['size'], $objdata['priority']);
				$object->arIsNewObject = true;
				debug("data: calling save");
				$object->save($objdata['properties']);
			}
			unset($object);
		}

		/*
			step 2
			if not skip templates
			if removeold
			remove old templates
			fi
			if update
			if forced
			save new templates
			else
			save new templates when newer
			fi
			else
			save new templates
			fi
			fi
		 */
		if(!($this->config['skiptemplates'] === true))
		{
			debug('work templates','all');
			if($id = $this->store->exists($path))
			{
				debug("templates: object exists",'all');
				$object = current(
						$this->store->call("system.get.phtml",
							array(),$this->store->get($path))
						);
				$templates=$object->store->get_filestore("templates");

				/*
					purge templates
				 */
				if(($this->config['dellalltemplates'] === true))
				{
					/* delete all current templates */
					$templates->purge($object->id);
					$object->data->config->pinp = array();
					$object->data->config->templates = array();
				}

				/*
					do something about those templates
				 */
				if(is_Array($objdata['data']->config->templates)){
					while(list($type,$tval) = each($objdata['data']->config->templates))
					{
						if(is_array($tval))
						{
							while(list($function,$nval) = each($tval))
							{
								if(is_array($nval))
								{
									while(list($language,$val) = each($nval))
									{
										$file = $type.".".$function.".".$language;
										debug("templates: working on template $file",'all');
										$pinp=new pinp("header","this->", "\$this->_");

										$template = base64_decode($val['template']);
										$compiled=$pinp->compile(strtr($template,"\r",""));

										if($templates->exists($object->id,$file))
										{
											if(
													($val['mtime'] >= $templates->mtime($object->id,$file)) ||
													($this->config['forcedata'] === true)
											  )
											{
												debug('templates: overwrite existing template','all');
												$templates->write($template, $object->id, $file.".pinp");
												$templates->touch($object->id,$file."pinp",$val['mtime']);
												$templates->write($compiled, $object->id, $file);
												$templates->touch($object->id,$file,$val['mtime']);
												$object->data->config->pinp[$type][$function][$language]=$object->id;
												/* is it a default template ? */
												if( $objdata['data']->config->templates[$type][$function][$language] === $objdata['id'])
												{
													$object->data->config->templates[$type][$function][$language] = $object->id;
												}
												else { // remove pinp template from default templates list
													if (isset($object->data->config->templates[$type][$function][$language])) {
														unset($object->data->config->templates[$type][$function][$language]);
														if (count($object->data->config->templates[$type][$function])==0) {
															unset($object->data->config->templates[$type][$function]);
															if (count($object->data->config->templates[$type])==0) {
																unset($object->data->config->templates[$type]);
															}

														}
													}
												}

											}
										}else
										{
											debug('templates: create template','all');
											$templates->write($template, $object->id, $file.".pinp");
											$templates->touch($object->id,$file."pinp",$val['mtime']);
											$templates->write($compiled, $object->id, $file);
											$templates->touch($object->id,$file,$val['mtime']);
											
											$object->data->config->pinp[$type][$function][$language]=$object->id;
											/* is it a default template ? */
											if( $objdata['data']->config->templates[$type][$function][$language] === $objdata['id'])
											{
												$object->data->config->templates[$type][$function][$language] = $object->id;
											}
											else { // remove pinp template from default templates list
												if (isset($object->data->config->templates[$type][$function][$language])) {
													unset($object->data->config->templates[$type][$function][$language]);
													if (count($object->data->config->templates[$type][$function])==0) {
														unset($object->data->config->templates[$type][$function]);
														if (count($object->data->config->templates[$type])==0) {
															unset($object->data->config->templates[$type]);
														}

													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				$object->save();
			}
		}

		/*
			step 3
			if not skip grants
			if removeold
			remove old grants
			fi
			if exists
			remove
			fi
			save grant
			fi
		 */
		if(!($this->config['skipgrants'] === true))
		{
			debug('work grants','all');
			if($id = $this->store->exists($path))
			{
				debug('grants: yeah the path exists','all');
				$object = current(
						$this->store->call("system.get.phtml",
							array(),$this->store->get($path))
						);
				if(is_array($objdata['data']->config->grants)){
					$object->data->config->grants = $objdata['data']->config->grants;
					$object->save();
				}
			}
		}

		/*
			step 4
			if not skip files
			if removeold
			remove old files
			fi
			if update
			if forced
			save new files
			else
			save new files when newer
			fi
			else
			save new files 
			fi
			fi
		 */
		if(!($this->config['skipfiles'] === true))
		{
			debug('work files','all');
			if($id = $this->store->exists($path))
			{
				$object = current(
						$this->store->call("system.get.phtml",
							array(),$this->store->get($path))
						);

				$files=$object->store->get_filestore("files");
				debug('files: yeah the path exists','all');

				if(is_Array($objdata[files]))
				{
					while(list($key,$val) = each($objdata[files]))
					{
						if($files->exists($object->id,$key))
						{
							if(
									($val['mtime'] >= $files->mtime($object->id,$key)) ||
									($this->config['forcefiles'] === true)
							  )
							{
								debug('files: overwrite existing file','all');
								$files->write(base64_decode($val[file]), $object->id, $key);
								$files->touch($object->id,$file,$val['mtime']);
							}
						}else
						{
							debug('files: create template','all');
							$files->write(base64_decode($val[file]), $object->id, $key);
							debug("files: touch $file with".$val['mtime'],'all');
							$files->touch($object->id,$file,$val['mtime']);
						}
					}
				}
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

	function parse($in,$store) {
		require_once($store->get_config("code")."modules/mod_pinp.phtml");
		$this->input = $in;
		$this->store = $store;
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