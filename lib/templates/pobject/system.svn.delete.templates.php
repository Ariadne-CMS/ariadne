<?php
	$ARCurrent->nolangcheck=true;
        if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$templates_info = array();
		if( is_array($this->data->config->pinp) ) {
			foreach ($this->data->config->pinp as $type => $values) {
				foreach ($values as $function => $templatelist) {
					foreach ($templatelist as $language => $template) {
						$filename = $type . "." . $function . "." . $language . ".pinp";
						$templates_info[$filename] = array(
							"type" => $type,
							"function" => $function,
							"language" => $language
						);
					}
				}
			}
		}
		if (is_array($templates)) {
			foreach ($templates as $path) {
				// _pobject.print_r.html.any.pinp
				$filename = basename($path);

				$underscore = substr($filename, 0, 1);
				$pinp = substr($filename, -5);

				if ($underscore != "_" || $pinp != ".pinp") {
					continue; // not a template
				}

				$filename = substr($filename, 1);

				if ($templates_info[$filename]) {
					$type = $templates_info[$filename]['type'];
					$language = $templates_info[$filename]['language'];
					$function = $templates_info[$filename]['function'];

					echo "<span class='svn_deletetemplateline'>Deleted ".$this->path.$function." (".$type.") [".$language."]</span>\n";

					$this->call("system.delete.layout.phtml", Array(
						"type"		=> $type,
						"function"	=> $function,
						"language"	=> $language
					));

					if ($this->error) {
						echo "\nError deleting ".$this->path.$meta['ar:function']." (".$meta['ar:type'].") [".$meta['ar:language']."]\n";
						echo $this->error."\n\n";
					}
				} else {
					echo "\nError deleting " . $filename . " (not found in template list)\n\n";
				}
			}
		}
	}
?>
