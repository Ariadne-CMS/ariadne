<?php
	/*
		This class is meant to make it easy to create inline editable pages. The class should not be instantiated, instead
		you just call (in pinp) edit::showSpan($data->name, "$nls[name]");
	
	*/

	class pinp_edit {

		function _setEditMode($mode=false, $template='user.edit.html', $target='_top') {
			global $mod_edit_data;
			$mod_edit_data['editmode']=$mode;
			$mod_edit_data['edittemplate']=$template;
			$mod_edit_data['edittarget']=$target;
		}

		function _getEditMode() {
			global $mod_edit_data;
			return $mod_edit_data['editmode'];
		}

		function _getEditTemplate() {
			global $mod_edit_data;
			return $mod_edit_data['edittemplate'];
		}

		function _getEditTarget() {
			global $mod_edit_data;
			return $mod_edit_data['edittarget'];
		}

		function registerDataField($name) {
			/* private method */
			global $mod_edit_data;
			$id=++$mod_edit_data['id'];
			echo "<script> parent.registerDataField('editable_$id','".AddCSlashes($name, ARESCAPE)."','".$this->path."',".$this->id."); </script>\n";
			return $id;
		}

		function _showInputText($var, $name, $title='') {
			if (pinp_edit::_getEditMode() && $this->CheckSilent('edit')) {
				$id=pinp_edit::registerDataField($name);
				echo "<input type='text' class='editable' id='editable_$id' ar:path='".$this->path."' ar:id='".$this->id."' title='$title' value='";
				echo $var;
				echo "'>";
			} else {
				echo $var;
			}
		}

		function _showSpan($var, $name, $title='') {
			if (pinp_edit::_getEditMode() && $this->CheckSilent('edit')) {
				$id=pinp_edit::registerDataField($name);
				echo "<span class='editable' id='editable_$id' ar:path='".$this->path."' ar:id='".$this->id."' title='$title'>";
				echo $var;
				echo "</span>";
			} else {
				echo $var;
			}
		}

		function _showDiv($var, $name, $title='') {
			if (pinp_edit::_getEditMode() && $this->CheckSilent('edit')) {
				$id=pinp_edit::registerDataField($name);
				echo "<div class='editable' id='editable_$id' ar:path='".$this->path."' ar:id='".$this->id."' title='$title'>";
				echo $var;
				echo "</div>";
			} else {
				echo $var;
			}
		}

		function _showLink($path, $extra='') {
			if (pinp_edit::_getEditMode()) {
				echo "<a href='".$this->make_url($path).pinp_edit::_getEditTemplate()."' $extra target='".pinp_edit::_getEditTarget()."'>";
			} else {
				echo "<a href='".$this->make_url($path)."' $extra>";				
			}
		}

		function _showEditableLink($path, $extra='', $url=false) {
			if (pinp_edit::_getEditMode()) {
				echo "<a onClick=\"event.cancelBubble=true\" onDblClick=\"top.location='".$this->make_url($path).pinp_edit::_getEditTemplate()."'\" $extra>";
			} else {
				if (!$url) {
					$url=$this->make_url($path);
				}
				echo "<a href='".$url."' $extra>";				
			}
		}
		
		function _showHref($path, $extra='') {
			if (pinp_edit::_getEditMode()) {
				echo "href='".$this->make_url($path).pinp_edit::_getEditTemplate()."' $extra target='".pinp_edit::_getEditTarget()."'";
			} else {
				echo "href='".$this->make_url($path)."'";
			}
		}
	}

?>