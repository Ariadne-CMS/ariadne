<?php

class export_wddx {

	function print_verbose($message){
		global $ARCurrent;
		if($ARCurrent->wddxoptions["verbose"]){
			print $message;
		}
	}

	function strtoxmldata($string) {
		return str_replace("]]>","]]&gt;",str_replace("]]&","]]&amp;",$string));
	}

	function xmldatatostr($string) {
		return str_replace("]]&amp;","]]&",str_replace("]]&gt;","]]>",$string));
	}

	function export($fp,&$object) {
		export_wddx::print_verbose('Exporting: '.$object->path."\n");
		fwrite($fp,"<var name=\"object".$object->id."\">\n");
		fwrite($fp,"<struct type=\"object\" class=\"".$object->type."\">\n");
		fwrite($fp,"<var name=\"id\">\n");
		fwrite($fp,"<number>".$object->id."</number>\n");
		fwrite($fp,"</var>\n");
		fwrite($fp,"<var name=\"path\">\n");
		fwrite($fp,"<string>".$object->path."</string>\n");
		fwrite($fp,"</var>\n");
		fwrite($fp,"<var name=\"type\">\n");
		fwrite($fp,"<string>".$object->type."</string>\n");
		fwrite($fp,"</var>\n");
		fwrite($fp,"<var name=\"vtype\">\n");
		fwrite($fp,"<string>".$object->vtype."</string>\n");
		fwrite($fp,"</var>\n");
		fwrite($fp,"<var name=\"priority\">\n");
		fwrite($fp,"<number>".$object->priority."</number>\n");
		fwrite($fp,"</var>\n");
		fwrite($fp,"<var name=\"size\">\n");
		fwrite($fp,"<number>".$object->size."</number>\n");
		fwrite($fp,"</var>\n");

		export_wddx::export_data($fp,"data", $object->data);
		export_wddx::export_properties($fp,$object);
		export_wddx::export_templates($fp,$object);
		export_wddx::export_files($fp,$object);

		fwrite($fp,"</struct>\n");
		fwrite($fp,"</var>\n");
	}

	function export_data($fp,$name, $value) {
		if (!is_null($value)) {
			fwrite($fp,"<var name=\"$name\">\n");
			if (is_bool($value)) {
					$value = ($value) ? 'true' : 'false';
				fwrite($fp,"<boolean>$value</boolean>\n");
			} else if (is_int($value)||is_real($value)||is_float($value)) {
				fwrite($fp,"<number>$value</number>\n");
			} else if (is_string($value) || ($value==="")) {
				fwrite($fp,"<string><![CDATA[".export_wddx::strtoxmldata($value)."]]></string>\n");
			} else if (is_array($value) || is_object($value)) {
				if (is_array($value)) {
					fwrite($fp,"<struct type=\"hash\">\n");
				} else {
					fwrite($fp,"<struct type=\"object\" class=\"".get_class($value)."\">\n");
				}
				while (list($key, $val)=each($value)) {
					export_wddx::export_data($fp,$key, $val);
				}
				fwrite($fp,"</struct>\n");
			}
			fwrite($fp,"</var>\n");
		}
	}

	function export_properties($fp,&$object) {
		$properties=$object->load_properties();
		export_wddx::export_data($fp,"properties",$properties);
	}

	function export_templates($fp,&$object) {
		if ($object->data->config->pinp) {
			fwrite($fp,"<var name=\"templates\">\n");
			fwrite($fp,"<struct type=\"hash\">\n");
			$templates=$object->store->get_filestore("templates");
			export_wddx::print_verbose("   Templates:\n");
			while (list($type, $functions)=each($object->data->config->pinp)) {
				fwrite($fp,"<var name=\"$type\">\n");
				fwrite($fp,"<struct type=\"hash\" >\n");
				while (list($function, $languages)=each($functions)) {
					fwrite($fp,"<var name=\"$function\">\n");
					fwrite($fp,"<struct type=\"hash\" >\n");
					while (list($language, $ids)=each($languages)) {
						$file=$type.".".$function.".".$language.".pinp";
						export_wddx::print_verbose('              ');
						export_wddx::print_verbose("[".$file."]: ");
						fwrite($fp,"<var name=\"$language\" >\n");
						fwrite($fp,"<struct type=\"hash\" class=\"file\" >\n");
						fwrite($fp,"<var name=\"template\">\n");
						$content = $templates->read($object->id, $file);
						fwrite($fp,"<string><![CDATA[");
						fwrite($fp,base64_encode($content));
						fwrite($fp,"]]></string></var>\n");
						fwrite($fp,"<var name=\"mtime\">\n");
						fwrite($fp,"<number>".$templates->mtime($object->id, $file)."</number>\n");
						fwrite($fp,"</var>\n");
						fwrite($fp,"<var name=\"ctime\">\n");
						fwrite($fp,"<number>".$templates->ctime($object->id, $file)."</number>\n");
						fwrite($fp,"</var>\n");
						fwrite($fp,"</struct>\n");
						fwrite($fp,"</var>");
						export_wddx::print_verbose("stored\n");
					}
					fwrite($fp,"</struct></var>\n");
				}
				fwrite($fp,"</struct></var>\n");
			}
			fwrite($fp,"</struct></var>\n");
			$templates->close();
		}
	}

	function export_files($fp,&$object) {
		$files = $object->store->get_filestore("files");
		$filearray = $files->ls($object->id);
		if( is_array($filearray) ) {
			export_wddx::print_verbose("       Files:\n");
			fwrite($fp,"<var name=\"files\">\n");
			fwrite($fp,"<struct type=\"hash\">\n");
			
			while( list( $key, $file ) = each($filearray) ) {
				export_wddx::print_verbose('              ');
				export_wddx::print_verbose("[".$name."]: ");
				fwrite($fp,"<var name=\"".$file."\">\n");
				fwrite($fp,"<struct type=\"hash\" >\n");
				fwrite($fp,"<var name=\"file\" >\n");
				fwrite($fp,"<string><![CDATA[");
				fwrite($fp,base64_encode($files->read($object->id, $file)));
				fwrite($fp,"]]></string>\n");
				fwrite($fp,"</var>\n");
				fwrite($fp,"<var name=\"mtime\">\n");
				fwrite($fp,"<number>".$files->mtime($object->id, $file)."</number>\n");
				fwrite($fp,"</var>\n");
				fwrite($fp,"<var name=\"ctime\">\n");
				fwrite($fp,"<number>".$files->ctime($object->id, $file)."</number>\n");
				fwrite($fp,"</var>\n");
				fwrite($fp,"</struct>\n");
				fwrite($fp,"</var>\n");
				export_wddx::print_verbose("stored\n");
			}
			
			
			fwrite($fp,"</struct>\n");
			fwrite($fp,"</var>\n");
		}
		$files->close();
	}


}

?>