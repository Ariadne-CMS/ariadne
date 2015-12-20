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
			$templatestore=$this->store->get_filestore("templates");

			foreach ($templates as $path) {
				// pobject.print_r.html.any.pinp
				$filename = basename($path);

				$pinp = substr($filename, -5);

				if ($pinp != ".pinp") {
					continue; // not a template
				}

				if ($templates_info[$filename]) {
					$type = $templates_info[$filename]['type'];
					$language = $templates_info[$filename]['language'];
					$function = $templates_info[$filename]['function'];

					if (!$templatestore->exists($this->id, $filename)) {
						echo "<span class='svn_deletetemplateline'>Deleted ".$this->path.$function." (".$type.") [".$language."]</span>\n";

						$this->call("system.delete.layout.phtml", array(
							"type"		=> $type,
							"function"	=> $function,
							"language"	=> $language
						));

						if ($this->error) {
							echo "\nError deleting ".$this->path.$meta['ar:function']." (".$meta['ar:type'].") [".$meta['ar:language']."]\n";
							echo $this->error."\n\n";
						}
					} else {
						echo "<span class='svn_deletetemplateline'>Skipped deleting ".$this->path.$function." (".$type.") [".$language."]</span>\n";
					}
				} else {
					echo "\nError deleting " . $filename . " (not found in template list)\n\n";
				}
			}
		}
	}
?>
