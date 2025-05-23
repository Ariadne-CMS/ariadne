<?php
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		// first make sure that the object is clean (data can only be set via
		// the defined interface: $arCallArgs)
		$this->data=current($this->get(".","system.get.data.phtml"));

		// check arguments, remove ".."
		$type=str_replace("..",".",$this->getvar("type")??'');
		$function=str_replace("..",".",$this->getvar("function")??'');
		$language=str_replace("..",".",$this->getvar("language")??'');

		if ($type && $function && $language) {
			$template_dir=$this->path."class=/".$type."/";
			$template_path=$template_dir.$function.".".$language;

			if (preg_match("/^[a-z0-9\._-]+$/i",$function)) {
				if ($this->data->config->pinp[$type][$function][$language]??null) {
					unset($this->data->config->pinp[$type][$function][$language]);
					if (count($this->data->config->pinp[$type][$function])==0) {
						unset($this->data->config->pinp[$type][$function]);
						if (count($this->data->config->pinp[$type])==0) {
							unset($this->data->config->pinp[$type]);
						}
					}
					if ($this->data->config->templates[$type][$function][$language]??null) {
						// Store the old template information in deleted_templates for SVN to use.
						if ($AR->SVN->enabled) {
							is_array($this->data->config->deleted_templates??null) ? false : $this->data->config->deleted_templates = array();
							is_array($this->data->config->deleted_templates[$type]??null) ? false : $this->data->config->deleted_templates[$type] = array();
							is_array($this->data->config->deleted_templates[$type][$function]??null) ? false : $this->data->config->deleted_templates[$type][$function] = array();
							$this->data->config->deleted_templates[$type][$function][$language] = $this->data->config->templates[$type][$function][$language];
						}

						unset($this->data->config->templates[$type][$function][$language]);
						if (count($this->data->config->templates[$type][$function])==0) {
							unset($this->data->config->templates[$type][$function]);
							if (count($this->data->config->templates[$type])==0) {
								unset($this->data->config->templates[$type]);
							}
						}
					}
					if ($this->data->config->privatetemplates[$type][$function]??null) {
						// Store the old template information in deleted_privatetemplates for SVN to use.
						if ($AR->SVN->enabled) {
							is_array($this->data->config->deleted_privatetemplates??null) ? false : $this->data->config->deleted_privatetemplates = array();
							is_array($this->data->config->deleted_privatetemplates[$type]??null) ? false : $this->data->config->deleted_privatetemplates[$type] = array();
							$this->data->config->deleted_privatetemplates[$type][$function] = $this->data->config->privatetemplates[$type][$function];
						}

						unset($this->data->config->privatetemplates[$type][$function]);
						if (count($this->data->config->privatetemplates[$type])==0) {
							unset($this->data->config->privatetemplates[$type]);
						}
					}
					$template=$type.".".$function.".".$language;
					$templates=$this->store->get_filestore("templates");
					$templates->remove($this->id, $template);
					$templates->remove($this->id, $template.".pinp");
					$templates->remove($this->id, $template.".inc");
					$this->save();
				}
			}
		}
	}
?>
