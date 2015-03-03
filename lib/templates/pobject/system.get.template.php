<?php
	if ( ( $this->CheckLogin('layout') || $this->CheckLogin('layout:read') ) && $this->CheckConfig() ) {
        $file = "";
        if ( isset($this->data->config->pinp[$type][$function][$language]) ) {
            $template=$type.".".$function.".".$language.".pinp";
            $templates=$this->store->get_filestore("templates");
            if ($templates->exists($this->id, $template)) {
                $ARCurrent->arResult = $templates->read($this->id, $template);
            } else {
				$ARCurrent->arResult = ar::error('Template not accessible', 501);
			}
        } else {
			$ARCurrent->arResult = ar::error('Template not found', 404);
		}
	}
?>
