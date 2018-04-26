<?php
class import_wddx {

	var $stack;
	var $nestdeep;
	var $input;
	var $xml_parser;
	var $store;
	var $config;
	var $linktable;
	var $seenconfig;

	function print_verbose($message){
		if($this->config['verbose']){
			print $message;
		}
	}

	function __construct($options){
		$this->input = null;
		$this->nestdeep = -4;
		$this->stack = array();
		$this->xml_parser = xml_parser_create();
		// use case-folding so we are sure to find the tag in $map_array
		xml_set_object($this->xml_parser, $this);
		xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($this->xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($this->xml_parser, "characterData");
		$this->config = $options;
		$this->linktable = array();
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
					$value = new baseObject();
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

			if ( is_string( $value['data'] ) && $this->seenconfig && ( $this->config['srcpath'] != $this->config['dstpath'] ) ) {
				$value['data'] = preg_replace( '#(^|[\'"])'.$this->config['srcpath'].'#i', '$1'.$this->config['dstpath'], $value['data'] );
			}

			$key = $var["data"];
			if(is_object($struct["data"]))
			{
				$struct["data"]->$key = $value["data"];
			} else if(is_array($struct["data"]))
			{
				$struct["data"][$key] = $value["data"];
			} else {
				echo "corrupted data found:\n";
				echo "#.#.#.#\n";
				print_r($struct["data"]);
				echo "#.#.#.#\n";
				print_r($this->stack);
				echo "#.#.#.#\n";
			}

			array_push($this->stack, $struct);
		} else if( $name == "struct"){
			if(!$this->seenconfig){
				// still waiting for the config
				if($this->nestdeep == -2){
					// -1 is the depth directly under <data>
					// -2 is the struct with the wddx configuration data
					$this->seenconfig = true;
					$config = (array_pop($this->stack));
					// oke do something with the config data please ?
					foreach ($config[data][options] as  $key => $value){
						if(!isset($this->config[$key])){
							$this->print_verbose("Taking config from wddx: $key => $value \n");
							$this->config[$key] = $value;
						}
					}
				}
			} else {
				if($this->nestdeep == 0 ){ // this is below
					$object = array_pop($this->stack);
					if(is_array($object) && is_array($object["data"])){
						$this->saveObject($object["data"]);
					}
				}
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
		debug("WDDX working on ".$objdata['path'],'all');

		if ( $this->config['srcpath'] != $this->config['dstpath'] ){
			if ( strpos( $objdata['path'], $this->config['srcpath'] ) === 0 ){
				$objdata['path'] = $this->config['dstpath'].substr($objdata['path'],strlen($this->config['srcpath']));
			}
		}

		if($this->config['prefix']){
			$path = $this->store->make_path($this->config['prefix'],"./".$objdata['path']);
		} else {
			$this->print_verbose("prefixless: ".$this->config['srcpath'].":".$objdata['path']."\n");
			$path = $this->store->make_path($this->config['srcpath'], $objdata['path']);
		}


		$this->print_verbose('Importing: '.$path.' ');
		if($this->linktable[$objdata['id']]){
			$this->linkObject($path,$this->linktable[$objdata['id']]);
		} else {
			$this->linktable[$objdata['id']] = $path;
			$this->storeObject($path,$objdata);
		}
	}

	function linkObject($path,$linkpath){
		$this->print_verbose(" ( linking ) \n");
		$this->store->link($linkpath,$path);
		if($object->error){
			debug("WDDX link: error during save");
			debug("WDDX link: ".$object->error);
		}
	}

	function storeObject($path,&$objdata){
	global $AR;
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
			debug('WDDX work data','all');
			if($id = $this->store->exists($path))
			{
				debug("WDDX data: object exists",'all');
				if(!is_object($object)){
					$object = current(
							$this->store->call("system.get.phtml",
								array(),$this->store->get($path))
							);
				}
				if(
						($object->lastchanged <= $objdata['lastchanged']) ||
						($this->config['forcedata'] === true))
				{
					$this->print_verbose(" ( updating ) \n");

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
					$object->save($objdata['properties']);
				} else {
					$this->print_verbose(" ( skipping ) \n");
				}
			} else
			{

				debug("WDDX data: object doesn't exists",'all');
				$this->print_verbose(" ( saving ) \n");
				$parent = $this->store->make_path($path,'..');
				if($parent == $path){ $parent = '..'; }
				$object = $this->store->newobject($path,
						$parent, $objdata['type'],
						$objdata['data'], 0, $objdata['lastchanged'],
						$objdata['vtype'], $objdata['size'], $objdata['priority']);

				$object->arIsNewObject = true;

				if(!$object->store->is_supported('fulltext')){
					unset($objdata['properties']['fulltext']);
				}

				$object->save($objdata['properties']);
				if($object->error){
					debug("WDDX data: error during save");
					debug("WDDX data: ".$object->error);
				}
				debug("WDDX data: done save");

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
			debug('WDDX work templates','all');
			if($id = $this->store->exists($path))
			{
				debug("WDDX templates: object exists",'all');
				if(!is_object($object)){
					$object = current(
							$this->store->call("system.get.phtml",
								array(),$this->store->get($path))
							);
				}
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
				if(is_Array($objdata['templates'])){
					$this->print_verbose("   Templates:\n");
					foreach($objdata['templates'] as $type => $tval)
					{
						if(is_array($tval))
						{
							foreach($tval as $function => $nval)
							{
								if(is_array($nval))
								{
									foreach($nval as $language => $val)
									{
										$file = $type.".".$function.".".$language;
										debug("WDDX templates: ".$object->id."working on template $file",'all');
										$pinp=new pinp($AR->PINP_Functions, "local->", "\$AR_this->_");

										$template = base64_decode($val['template']);
										$compiled=$pinp->compile(strtr($template,"\r",""));
										$this->print_verbose('              ');
										$this->print_verbose("[".$file."]: ");

										if($templates->exists($object->id,$file))
										{
											if(
													($val['mtime'] >= $templates->mtime($object->id,$file)) ||
													($this->config['forcedata'] === true)
											  )
											{
												debug('WDDX templates: overwrite existing template','all');
												$this->print_verbose(" saving\n");
												$templates->write($template, $object->id, $file.".pinp");
												$templates->touch($object->id,$file.".pinp",$val['mtime']);
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

											} else {
												$this->print_verbose(" skipping\n");
											}
										}else
										{
											debug('WDDX templates: create template','all');
											$this->print_verbose(" saving\n");
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
								} else {
									debug("WDDX template: nval != array",'all');
								}
							}
						} else {
							debug("WDDX template: tval != array",'all');
						}
					}
				} else {
					debug("WDDX template: templates != array",'all');
				}
				$changed = true;
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
			debug('WDDX work grants','all');
			if($id = $this->store->exists($path))
			{
				debug('WDDX grants: yeah the path exists','all');
				if(!is_object($object)){
					$object = current(
							$this->store->call("system.get.phtml",
								array(),$this->store->get($path))
							);
				}
				if(is_array($objdata['data']->config->grants)){
					$this->print_verbose("   Grants: installing\n");
					$object->data->config->grants = $objdata['data']->config->grants;
					$changed = true;
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
			debug('WDDX work files','all');
			if($id = $this->store->exists($path))
			{
				if(!is_object($object)){
					$object = current(
							$this->store->call("system.get.phtml",
								array(),$this->store->get($path))
							);
				}

				$files=$object->store->get_filestore("files");
				debug('WDDX files: yeah the path exists','all');

				if(is_Array($objdata[files]) && count($objdata[files]) > 0)
				{
					$this->print_verbose("       Files:\n");
					foreach($objdata[files] as $key => $val)
					{
						$this->print_verbose('              ');
						$this->print_verbose("[".$key."]: ");
						if($files->exists($object->id,$key))
						{
							if(
									($val['mtime'] >= $files->mtime($object->id,$key)) ||
									($this->config['forcefiles'] === true)
							  )
							{
								debug('WDDX files: overwrite existing file','all');
								$this->print_verbose(" updating\n");
								$files->write(base64_decode($val[file]), $object->id, $key);
								$files->touch($object->id,$file,$val['mtime']);
							} else {
								$this->print_verbose(" skipping\n");
							}
						}else
						{
							$this->print_verbose(" saving\n");
							debug('WDDX files: create template','all');
							$files->write(base64_decode($val[file]), $object->id, $key);
							debug("WDDX files: touch $file with".$val['mtime'],'all');
							$files->touch($object->id,$file,$val['mtime']);
						}
					}
				}
			}
		}
		// save the object
		if($changed){
			$object->save();
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
		$this->seenconfig = false;
		while (!feof($this->input)){
			$data = fgets($this->input, 65535);
			if (!xml_parse($this->xml_parser, $data, feof($this->input))) {
				die(sprintf("XML error: %s at line %d column %d \n",
							xml_error_string(xml_get_error_code($this->xml_parser)),
							xml_get_current_line_number($this->xml_parser),
							xml_get_current_column_number($this->xml_parser)));
			}
		}
		xml_parser_free($this->xml_parser);
	}
}
