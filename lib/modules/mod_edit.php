<?php
	/*
		This class is meant to make it easy to create inline editable pages. The class should not be instantiated, instead
		you just call (in pinp) edit::showSpan($data->name, "$nls[name]");

	*/

	include_once($this->store->get_config("code")."modules/mod_page.php");

	class edit {

		public static function setEditMode($mode=false, $template='user.edit.html', $target='_top') {
			global $mod_edit_data;
			$mod_edit_data['editmode']=$mode;
			$mod_edit_data['edittemplate']=$template;
			$mod_edit_data['edittarget']=$target;
		}

		public static function getEditMode() {
			global $mod_edit_data;
			return $mod_edit_data['editmode'];
		}

		public static function getEditTemplate() {
			global $mod_edit_data;
			return $mod_edit_data['edittemplate'];
		}

		public static function getEditTarget() {
			global $mod_edit_data;
			return $mod_edit_data['edittarget'];
		}

		public static function registerDataField($name) {
			/* private method */
			global $mod_edit_data;
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$id=++$mod_edit_data['id'];
			echo "<script> parent.registerDataField('editable_$id','".AddCSlashes($name, ARESCAPE)."','".$me->path."',".$me->id."); </script>\n";
			return $id;
		}

		public static function requireDataField($name, $title) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (edit::getEditMode()) {
				echo "<script> parent.requireDataField('".AddCSlashes($name, ARESCAPE)."',".$me->id.",'".AddCSlashes($title, ARESCAPE)."'); </script>\n";
			}
		}

		public static function showInputText($var, $name, $title='', $extra='') {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$id = null;
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id=edit::registerDataField($name);
				echo "<input type='text' class='editable' id='editable_$id' ar:path='".$me->path."' ar:id='".$me->id."' title='$title' value=\"";
				echo htmlspecialchars($var);
				echo "\" $extra>";
			} else if (!edit::isEmpty($var)) {
				echo $var;
			}
			return $id;
		}

		public static function showInput($var, $name, $title, $type='text', $extra='') {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$id = null;
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id=edit::registerDataField($name);
				echo "<input name='$name' type='$type' class='editable' id='editable_$id' ar:path='".$me->path."' ar:id='".$me->id."' title='$title' value=\"";
				echo htmlspecialchars($var);
				echo "\" $extra>";
			} else if (!edit::isEmpty($var)) {
				echo $var;
			}
			return $id;
		}

		public static function registerGroup($name, $id) {
			/* private method - adds $id to group $name, a change in any member of the group, forces dirty on all members */
			echo "<script> parent.registerGroup('$name', 'editable_$id'); </script>\n";
		}

		public static function showCheckbox($var, $name, $title, $extra='', $group='' ) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$id = null;
			if(edit::getEditMode() && $me->CheckSilent('edit')) {
				$id=edit::registerDataField($name);
				if ($group) {
					edit::registerGroup($group, $id);
				}
				$checked = "";
				if( $var ) {
					$checked = "checked";
				}
				echo "<input name='$name' type='checkbox' class='editable' id='editable_$id' ar:path='".$me->path."' ar:id='".$me->id."' title='$title' value=\"1\" $extra $checked>";
			} else if( !edit::isEmpty($var)) {
				echo $var;
			}
			return $id;
		}

		public static function showSelect($var, $name, $title, $list, $bykey=false, $extra='') {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$id = null;
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id=edit::registerDataField($name);
				echo "<select class='editable' id='editable_$id' ar:path='".$me->path."' ar:id='".$me->id."' title='$title'>";
				foreach ($list as $key => $value) {
					echo "<option";
					if ($bykey) {
						echo " value=\"$key\"";
						if ($key==$var) {
							echo " selected";
						}
					} else {
						echo " value=\"$value\"";
						if ($value==$var) {
							echo " selected";
						}
					}
					echo ">$value</option>\n";
				}
				echo "</select>";
			} else if (!edit::isEmpty($var)) {
				echo $var;
			}
			return $id;
		}

		public static function showSpan($var, $name, $title='', $extra='') {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$id = null;
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id=edit::registerDataField($name);
				echo "<span class='editable' id='editable_$id' ar:path='".$me->path."' ar:id='".$me->id."' title='$title' $extra>";
				echo $var;
				echo "</span>";
			} else if (!edit::isEmpty($var)) {
				echo page::stripARNameSpace($var);
			}
			return $id;
		}

		public static function showDiv($var, $name, $title='', $extra='') {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			$id = null;
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id=edit::registerDataField($name);
				echo "<div class='editable' id='editable_$id' ar:path='".$me->path."' ar:id='".$me->id."' title='$title' $extra>";
				echo $var;
				echo "</div>";
			} else if (!edit::isEmpty($var)) {
				echo page::stripARNameSpace($var);
			}
			return $id;
		}

		public static function showLink($path='', $extra='') {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (edit::getEditMode()) {
				echo "<a href='".$me->make_url($path).edit::getEditTemplate()."' $extra target='".edit::getEditTarget()."'>";
			} else {
				echo "<a href='".$me->make_url($path)."' $extra>";
			}
		}

		public static function showEditableLink($path='', $extra='', $url=false) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (edit::getEditMode()) {
				echo "<a onClick=\"event.cancelBubble=true\" onDblClick=\"top.location='".$me->make_url($path).edit::getEditTemplate()."'\" $extra>";
			} else {
				if (!$url) {
					$url=$me->make_url($path);
				}
				echo "<a href='".$url."' $extra>";
			}
		}

		public static function showHref($path='', $extra='') {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (edit::getEditMode()) {
				echo "href='".$me->make_url($path).edit::getEditTemplate()."' $extra target='".edit::getEditTarget()."'";
			} else {
				echo "href='".$me->make_url($path)."'";
			}
		}

		public static function showUrl($path='') {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (edit::getEditMode()) {
				echo $me->make_url($path).edit::getEditTemplate();
			} else {
				echo $me->make_url($path);
			}
		}

		public static function isEmpty($var) {
			return (trim(preg_replace('/&nbsp;/',' ',strip_tags($var, '<img>')))=="");
		}
	}

	class pinp_edit {

		public static function _setEditMode($mode=false, $template='user.edit.html', $target='_top') {
			return edit::setEditMode($mode, $template, $target);
		}

		public static function _getEditMode() {
			return edit::getEditMode();
		}

		public static function _getEditTemplate() {
			return edit::getEditTemplate();
		}

		public static function _getEditTarget() {
			return edit::getEditTarget();
		}

		public static function _registerDataField($name) {
			return edit::registerDataField($name);
		}

		public static function _registerGroup($name, $id) {
			return edit::registerGroup($name, $id);
		}

		public static function _requireDataField($name, $title) {
			return edit::requireDataField($name, $title);
		}

		public static function _showInputText($var, $name, $title='', $extra='') {
			return edit::showInputText($var, $name, $title, $extra);
		}

		public static function _showInput($var, $name, $title='', $type='text', $extra='') {
			return edit::showInput($var, $name, $title, $type, $extra);
		}

		public static function _showCheckbox($var, $name, $title='', $extra='', $group='') {
			return edit::showCheckbox($var, $name, $title, $extra, $group);
		}

		public static function _showSelect($var, $name, $title='', $list, $bykey=false, $extra='') {
			return edit::showSelect($var, $name, $title, $list, $bykey, $extra);
		}

		public static function _showSpan($var, $name, $title='', $extra='') {
			return edit::showSpan($var, $name, $title, $extra);
		}

		public static function _showDiv($var, $name, $title='', $extra='') {
			return edit::showDiv($var, $name, $title, $extra);
		}

		public static function _showLink($path='', $extra='') {
			return edit::showLink($path, $extra);
		}

		public static function _showEditableLink($path='', $extra='', $url=false) {
			return edit::showEditableLink($path, $extra, $url);
		}

		public static function _showHref($path='') {
			return edit::showHref($path);
		}

		public static function _showUrl($path='') {
			return edit::showUrl($path);
		}

		public static function _isEmpty($var) {
			return edit::isEmpty($var);
		}
	}
