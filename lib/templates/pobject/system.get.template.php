<?php
	if ( ( $this->CheckLogin('layout') || $this->CheckLogin('layout:read') ) && $this->CheckConfig() ) {
        $file = "";
        if ($this->data->config->pinp[$type][$function][$language]) {
            $template=$type.".".$function.".".$language.".pinp";
            $templates=$this->store->get_filestore("templates");
            if ($templates->exists($this->id, $template)) {
                $file=$templates->read($this->id, $template);
            }
        }
		$ARCurrent->arResult = $file;
	}
?>