<?php

	class pinp_edit {

		function _setEditmode($mode=false) {
			global $mod_edit_data;
			$mod_edit_data['editmode']=$mode;
		}

		function _getEditmode() {
			global $mod_edit_data;
			return $mod_edit_data['editmode'];
		}

		function _showSpan($var, $name) {
			global $mod_edit_data;
			if ($mod_edit_data['editmode']) {
				$id=++$mod_edit_data['id'];
				echo "<span class='editable' id='editable_$id' ar:path='".$this->path."' ar:id='".$this->id."'>";
				echo $var;
				echo "</span>";
				$mod_edit_data['formdata'][$this->path][$name][]=$id;
			} else {
				echo $var;
			}
		}

		function _showDiv($var, $name) {
			global $mod_edit_data;
			if ($mod_edit_data['editmode']) {
				$id=++$mod_edit_data['id'];
				echo "<div class='editable' id='editable_$id' ar:path='".$this->path."' ar:id='".$this->id."'>";
				echo $var;
				echo "</div>";
				$mod_edit_data['formdata'][$this->path][$name][]=$id;
			} else {
				echo $var;
			}
		}

		function _openForm() {
			global $mod_edit_data, $formcounter;
			if ($mod_edit_data['editmode']) {
				$id=++$mod_edit_data['formcounter'];
				echo "<form id='edit_form_".$id."' ar:path='".$this->path."' ar:id='".$this->id."'>";
				$mod_edit_data['forms'][$this->path][]=$id;
			}
		}

		function _closeForm() {
			global $mod_edit_data;
			if ($mod_edit_data['editmode']) {
				echo "</form>";
			}
		}

		function _registerData() {
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