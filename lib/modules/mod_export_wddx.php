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

	function header($fp){
		global $ARCurrent;
		fwrite($fp,'<?xml version="1.0" encoding="UTF-8"?>'."\n");
		fwrite($fp,
			"<wddxPacket version=\"1.0\">\n".
				"<header>\n".
					"<comment>Ariadne Export File</comment>\n".
				"</header>\n".
				"<data>\n".
					"<struct type=\"hash\">\n"
			);
		export_wddx::export_data($fp,'version',2);
		export_wddx::export_data($fp,'options',$ARCurrent->wddxoptions);
		fwrite($fp,
					"</struct>\n".
					"<struct type=\"hash\">\n"
			);
	}

	function footer($fp){
		fwrite($fp,
					"</struct>\n".
				"</data>\n".
			"</wddxPacket>\n"
			);
	}


	function export($fp,&$object) {
		global $ARCurrent;
		if($ARCurrent->wddxoptions['srcpath'] != $ARCurrent->wddxoptions['dstpath']){
			$exportpath = $ARCurrent->wddxoptions['dstpath'].substr($object->path,strlen($ARCurrent->wddxoptions['srcpath']));
		} else {
			$exportpath = $object->path;
		}
		export_wddx::print_verbose('Exporting: ['.$object->path.'] as ['.$exportpath."]\n");

		fwrite($fp,"<var name=\"object".$object->id."\">\n");
		fwrite($fp,"<struct type=\"object\" class=\"".$object->type."\">\n");
		fwrite($fp,"<var name=\"id\">\n");
		fwrite($fp,"<number>".$object->id."</number>\n");
		fwrite($fp,"</var>\n");
		fwrite($fp,"<var name=\"path\">\n");
		fwrite($fp,"<string>".$exportpath."</string>\n");
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

		if(!$ARCurrent->wddxoptions["export_skipdata"]){
			if($ARCurrent->wddxoptions["export_skipgrants"]){
				unset($object->data->config->grants);
			}
			export_wddx::export_data($fp,"data", $object->data);
			export_wddx::export_properties($fp,$object);
		}
		if(!$ARCurrent->wddxoptions["export_skiptemplates"]){
			export_wddx::export_templates($fp,$object);
		}
		if(!$ARCurrent->wddxoptions["export_skipfiles"]){
			export_wddx::export_files($fp,$object);
		}

		fwrite($fp,"</struct>\n");
		fwrite($fp,"</var>\n");
	}

	function export_data($fp,$name, $value) {
		global $ARCurrent;
		if (!is_null($value)) {
			fwrite($fp,"<var name=\"$name\">\n");
			if (is_bool($value)) {
					$value = ($value) ? 'true' : 'false';
				fwrite($fp,"<boolean>$value</boolean>\n");
			} else if (is_int($value)||is_real($value)||is_float($value)) {
				fwrite($fp,"<number>$value</number>\n");
			} else if (is_string($value) || ($value==="")) {
				if($ARCurrent->wddxoptions['srcpath'] != $ARCurrent->wddxoptions['dstpath']){
					$value = preg_replace( '#(^|[\'"])'.$ARCurrent->wddxoptions['srcpath'].'#i', '$1'.$ARCurrent->wddxoptions['dstpath'], $value);
				}
				fwrite($fp,"<string><![CDATA[".export_wddx::strtoxmldata($value)."]]></string>\n");
			} else if (is_array($value) || is_object($value)) {
				if (is_array($value)) {
					fwrite($fp,"<struct type=\"hash\">\n");
				} else {
					fwrite($fp,"<struct type=\"object\" class=\"".get_class($value)."\">\n");
				}
				foreach( $value as $key => $val){
					export_wddx::export_data($fp,$key, $val);
				}
				fwrite($fp,"</struct>\n");
			}
			fwrite($fp,"</var>\n");
		}
	}

	function export_properties($fp,&$object) {
		$properties=$object->load_properties('%'); // get properties for all scopes
		export_wddx::export_data($fp,"properties",$properties);
	}

	function export_templates($fp,&$object) {
		if ($object->data->config->pinp) {
			fwrite($fp,"<var name=\"templates\">\n");
			fwrite($fp,"<struct type=\"hash\">\n");
			$templates=$object->store->get_filestore("templates");
			export_wddx::print_verbose("   Templates:\n");
			foreach($object->data->config->pinp as $type =>  $functions){
				fwrite($fp,"<var name=\"$type\">\n");
				fwrite($fp,"<struct type=\"hash\" >\n");
				foreach($functions as $function => $languages){
					fwrite($fp,"<var name=\"$function\">\n");
					fwrite($fp,"<struct type=\"hash\" >\n");
					foreach($languages as $language => $ids){
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
		if( is_array($filearray) && count($filearray) >= 1) {
			export_wddx::print_verbose("       Files:\n");
			fwrite($fp,"<var name=\"files\">\n");
			fwrite($fp,"<struct type=\"hash\">\n");

			foreach( $filearray as $file){
				export_wddx::print_verbose('              ');
				export_wddx::print_verbose("[".$file."]: ");
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
