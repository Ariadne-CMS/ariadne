<?php

class export_wddx {



	function strtoxmldata($string) {
		return str_replace("]]>","]]&gt;",str_replace("]]&","]]&amp;",$string));
	}

	function xmldatatostr($string) {
		return str_replace("]]&amp;","]]&",str_replace("]]&gt;","]]>",$string));
	}

	function export(&$object) {
		echo "<var name=\"object".$object->id."\">\n";
		echo "<struct type=\"object\" class=\"".$object->type."\">\n";
		echo "<var name=\"id\">\n";
		echo "<number>".$object->id."</number>\n";
		echo "</var>\n";
		echo "<var name=\"path\">\n";
		echo "<string>".$object->path."</string>\n";
		echo "</var>\n";
		echo "<var name=\"type\">\n";
		echo "<string>".$object->type."</string>\n";
		echo "</var>\n";
		echo "<var name=\"vtype\">\n";
		echo "<string>".$object->vtype."</string>\n";
		echo "</var>\n";
		echo "<var name=\"priority\">\n";
		echo "<number>".$object->priority."</number>\n";
		echo "</var>\n";
		echo "<var name=\"size\">\n";
		echo "<number>".$object->size."</number>\n";
		echo "</var>\n";

		export_wddx::export_data("data", $object->data);
		export_wddx::export_properties($object);
		export_wddx::export_templates($object);
		export_wddx::export_files($object);

		echo "</struct>\n";
		echo "</var>\n";
	}

	function export_data($name, $value) {
		if (!is_null($value)) {
			echo "<var name=\"$name\">\n";
			if (is_bool($value)) {
					$value = ($value) ? 'true' : 'false';
				echo "<boolean>$value</boolean>\n";
			} else if (is_int($value)||is_real($value)||is_float($value)) {
				echo "<number>$value</number>\n";
			} else if (is_string($value) || ($value==="")) {
				echo "<string><![CDATA[".export_wddx::strtoxmldata($value)."]]></string>\n";
			} else if (is_array($value) || is_object($value)) {
				if (is_array($value)) {
					echo "<struct type=\"hash\">\n";
				} else {
					echo "<struct type=\"object\" class=\"".get_class($value)."\">\n";
				}
				while (list($key, $val)=each($value)) {
					export_wddx::export_data($key, $val);
				}
				echo "</struct>\n";
			}
			echo "</var>\n";
		}
		flush();
	}

	function export_properties(&$object) {
		$properties=$object->load_properties();
		export_wddx::export_data("properties",$properties);
	}

	function export_templates(&$object) {
		if ($object->data->pinp) {
			echo "<var name=\"templates\">\n";
			echo "<struct type=\"hash\">\n";
			$templates=$object->store->get_filestore("templates");
			while (list($type, $functions)=each($object->data->pinp)) {
				echo "<var name=\"$type\">\n";
				echo "<struct type=\"hash\" >\n";
				while (list($function, $languages)=each($functions)) {
					echo "<var name=\"$function\">\n";
					echo "<struct type=\"hash\" >\n";
					while (list($language, $ids)=each($languages)) {
						echo "<var name=\"$language\" >\n";
						echo "<struct type=\"hash\" class=\"file\" >\n";
						echo "<var name=\"template\">\n";
						echo "<string><![CDATA[";
						$file=$type.".".$function.".".$language.".pinp";
						echo base64_encode($templates->read($object->id, $file));
						echo "]]></string></var>\n";
						echo "<var name=\"mtime\">\n";
						echo "<number>".$templates->mtime($object->id, $file)."</number>\n";
						echo "</var>\n";
						echo "<var name=\"ctime\">\n";
						echo "<number>".$templates->ctime($object->id, $file)."</number>\n";
						echo "</var>\n";
						echo "</struct>\n";
						echo "</var>";
					}
					echo "</struct></var>\n";
				}
				echo "</struct></var>\n";
			}
			echo "</struct></var>\n";
			$templates->close();
		}
	}

	function export_files(&$object) {
		$files = $object->store->get_filestore("files");
		$filearray = $files->ls($object->id);
		if( is_array($filearray) ) {
			echo "<var name=\"files\">\n";
			echo "<struct type=\"hash\">\n";
			
			while( list( $key, $file ) = each($filearray) ) {
				echo "<var name=\"".$file."\">\n";
				echo "<struct type=\"hash\" >\n";
				echo "<var name=\"file\" >\n";
				echo "<string><![CDATA[";
				echo base64_encode($files->read($object->id, $file));
				echo "]]></string>\n";
				echo "</var>\n";
				echo "<var name=\"mtime\">\n";
				echo "<number>".$files->mtime($object->id, $file)."</number>\n";
				echo "</var>\n";
				echo "<var name=\"ctime\">\n";
				echo "<number>".$files->ctime($object->id, $file)."</number>\n";
				echo "</var>\n";
				echo "</struct>\n";
				echo "</var>\n";
			}
			
			
			echo "</struct>\n";
			echo "</var>\n";
		}
		$files->close();
	}


}

?>