<?php
	/*
		This class is meant to make it easy to create inline editable pages. The class should not be instantiated, instead
		you just call (in pinp) edit::showSpan($data->name, "$nls[name]");
	
	*/

	class edit {

		function setEditMode($mode=false, $template='user.edit.html', $target='_top') {
			global $mod_edit_data;
			$mod_edit_data['editmode']=$mode;
			$mod_edit_data['edittemplate']=$template;
			$mod_edit_data['edittarget']=$target;
		}

		function getEditMode() {
			global $mod_edit_data;
			return $mod_edit_data['editmode'];
		}

		function getEditTemplate() {
			global $mod_edit_data;
			return $mod_edit_data['edittemplate'];
		}

		function getEditTarget() {
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

		function showInputText($var, $name, $title='') {
			if (edit::getEditMode() && $this->CheckSilent('edit')) {
				$id=edit::registerDataField($name);
				echo "<input type='text' class='editable' id='editable_$id' ar:path='".$this->path."' ar:id='".$this->id."' title='$title' value='";
				echo $var;
				echo "'>";
			} else if (!edit::isEmpty($var)) {
				echo $var;
			}
		}

		function showInput($var, $name, $title, $type='text', $extra='') {
			if (edit::getEditMode() && $this->CheckSilent('edit')) {
				$id=edit::registerDataField($name);
				echo "<input type='$type' class='editable' id='editable_$id' ar:path='".$this->path."' ar:id='".$this->id."' title='$title' value='";
				echo $var;
				echo "' $extra>";			
			} else if (!edit::isEmpty($var)) {
				echo $var;
			}
		}

		function showSpan($var, $name, $title='') {
			if (edit::getEditMode() && $this->CheckSilent('edit')) {
				$id=edit::registerDataField($name);
				echo "<span class='editable' id='editable_$id' ar:path='".$this->path."' ar:id='".$this->id."' title='$title'>";
				echo $var;
				echo "</span>";
			} else if (!edit::isEmpty($var)) {
				echo $var;
			}
		}

		function showDiv($var, $name, $title='') {
			if (edit::getEditMode() && $this->CheckSilent('edit')) {
				$id=edit::registerDataField($name);
				echo "<div class='editable' id='editable_$id' ar:path='".$this->path."' ar:id='".$this->id."' title='$title'>";
				echo $var;
				echo "</div>";
			} else if (!edit::isEmpty($var)) {
				echo $var;
			}
		}

		function showLink($path='', $extra='') {
			if (edit::getEditMode()) {
				echo "<a href='".$this->make_url($path).edit::getEditTemplate()."' $extra target='".edit::getEditTarget()."'>";
			} else {
				echo "<a href='".$this->make_url($path)."' $extra>";				
			}
		}

		function showEditableLink($path='', $extra='', $url=false) {
			if (edit::getEditMode()) {
				echo "<a onClick=\"event.cancelBubble=true\" onDblClick=\"top.location='".$this->make_url($path).edit::getEditTemplate()."'\" $extra>";
			} else {
				if (!$url) {
					$url=$this->make_url($path);
				}
				echo "<a href='".$url."' $extra>";				
			}
		}
		
		function showHref($path='', $extra='') {
			if (edit::getEditMode()) {
				echo "href='".$this->make_url($path).edit::getEditTemplate()."' $extra target='".edit::getEditTarget()."'";
			} else {
				echo "href='".$this->make_url($path)."'";
			}
		}

		function isEmpty($var) {
			return trim(ereg_replace('&nbsp;',' ',strip_tags($nlsdata->summary, '<img>'))); 
		}
	}

	class pinp_edit {

		function _setEditMode($mode=false, $template='user.edit.html', $target='_top') {
			return edit::setEditMode($mode, $template, $target);
		}

		function _getEditMode() {
			return edit::getEditMode();
		}

		function _getEditTemplate() {
			return edit::getEditTemplate();
		}

		function _getEditTarget() {
			return edit::getEditTarget();
		}

		function _registerDataField($name) {
			return edit::registerDataField($name);
		}

		function _showInputText($var, $name, $title='') {
			return edit::showInputText($var, $name, $title);
		}

		function _showInput($var, $name, $title, $type='text', $extra='') {
			return edit::showInput($var, $name, $title, $type, $extra);
		}

		function _showSpan($var, $name, $title='') {
			return edit::showSpan($var, $name, $title);
		}

		function _showDiv($var, $name, $title='') {
			return edit::showDiv($var, $name, $title);
		}

		function _showLink($path, $extra='') {
			return edit::showLink($path, $extra);
		}

		function _showEditableLink($path, $extra='', $url=false) {
			return edit::showEditableLink($path, $extra, $url);			
		}
		
		function _showHref($path) {
			return edit::showHref($path);
		}

		function _isEmpty($var) {
			return edit::isEmpty($var);
		}
	}


?>