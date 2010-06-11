<?php
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		// first make sure that the object is clean (data can only be set via 
		// the defined interface: $arCallArgs)
		$this->data=current($this->get(".","system.get.data.phtml"));

		// check arguments, remove ".."
		$type=str_replace("..",".",$this->getvar("type"));
		$function=str_replace("..",".",$this->getvar("function"));
		$language=str_replace("..",".",$this->getvar("language"));

		$template_dir=$this->path."class=/".$type."/";
		$template_path=$template_dir.$function.".".$language;

		if (eregi("^[a-z0-9\._-]+$",$function)) {
			if (isset($this->data->config->pinp[$type][$function][$language])) {
				unset($this->data->config->pinp[$type][$function][$language]);
				if (count($this->data->config->pinp[$type][$function])==0) {
					unset($this->data->config->pinp[$type][$function]);
					if (count($this->data->config->pinp[$type])==0) {
						unset($this->data->config->pinp[$type]);
					}
				}
				if (isset($this->data->config->templates[$type][$function][$language])) {
					unset($this->data->config->templates[$type][$function][$language]);
					if (count($this->data->config->templates[$type][$function])==0) {
						unset($this->data->config->templates[$type][$function]);
						if (count($this->data->config->templates[$type])==0) {
							unset($this->data->config->templates[$type]);
						}
					}
				}
				$template=$type.".".$function.".".$language;
				$templates=$this->store->get_filestore("templates");
				$templates->remove($this->id, $template);
				$templates->remove($this->id, $template.".pinp");
				$this->save();
			}
		}
	}
?>