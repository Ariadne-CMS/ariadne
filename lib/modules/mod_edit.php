<?php

	class pinp_edit {

		function _setEditmode(&$object, $mode=false) {
			global $mod_edit_data;
			$mod_edit_data['editmode']=$mode;
		}

		function _getEditmode(&$object) {
			global $mod_edit_data;
			return $mod_edit_data['editmode'];
		}

		function _showSpan(&$object, $var, $name) {
			global $mod_edit_data;
			if ($mod_edit_data['editmode']) {
				$id=++$mod_edit_data['id'];
				echo "<span class='editable' id='editable_$id' ar:path='".$object->path."' ar:id='".$object->id."'>";
				echo $var;
				echo "</span>";
				$mod_edit_data['formdata'][$object->path][$name][]=$id;
			} else {
				echo $var;
			}
		}

		function _showDiv(&$object, $var, $name) {
			global $mod_edit_data;
			if ($mod_edit_data['editmode']) {
				$id=++$mod_edit_data['id'];
				echo "<div class='editable' id='editable_$id' ar:path='".$object->path."' ar:id='".$object->id."'>";
				echo $var;
				echo "</div>";
				$mod_edit_data['formdata'][$object->path][$name][]=$id;
			} else {
				echo $var;
			}
		}

		function _openForm(&$object) {
			global $mod_edit_data, $formcounter;
			if ($mod_edit_data['editmode']) {
				$id=++$mod_edit_data['formcounter'];
				echo "<form id='edit_form_".$id."' ar:path='".$object->path."' ar:id='".$object->id."'>";
				$mod_edit_data['forms'][$object->path][]=$id;
			}
		}

		function _closeForm(&$object) {
			global $mod_edit_data;
			if ($mod_edit_data['editmode']) {
				echo "</form>";
			}
		}

		function _registerData(&$object) {
			global $mod_edit_data;
			if ($mod_edit_data['editmode']) {
				echo "<script>\n";
				echo "  window.editableElements=new Array();\n";
				echo "  window.idList=new Array();\n";
				reset($mod_edit_data['formdata']);
				while (list($path, $elementlist)=each($mod_edit_data['formdata'])) {
					echo "  window.editableElements['$path']=new Array();\n";
					while (list($name, $idlist)=each($elementlist)) {
						echo "  window.editableElements['$path']['$name']=new Array();\n";
						while (list($key, $id)=each($idlist)) {
							echo "  window.editableElements['$path']['$name'][$key]='editable_$id';\n";
						}
						reset($idlist);
						while (list($key, $id)=each($idlist)) {
							echo "  window.idList['editable_$id']=editableElements['$path']['$name'];\n";
						}
					}
				}
				echo "  window.editableForms=new Array();\n";
				if (is_array($mod_edit_data['forms'])) {
					reset($mod_edit_data['forms']);
					while (list($path, $idlist)=each($mod_edit_data['forms'])) {
						echo "  window.editableForms['$path']=new Array();\n";
						while (list($key, $id)=each($idlist)) {
							echo "  window.editableForms['$path'][$key]='edit_form_$id';\n";
						}
					}
				}
				echo "</script>";
			}
		}
	}

?>