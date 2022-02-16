<?php
	/*
		This class is meant to make it easy to create inline editable pages. The class should not be instantiated, instead
		you just call (in pinp) edit::showSpan($data->name, "$nls[name]");
	
	*/

	include_once($this->store->get_config("code")."modules/mod_page.php");

	class edit {
		public static function reset() {
			global $AR;
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];

			if (edit::getEditMode()) {
				$lang = $me->_getvar('vdLanguage');
				if (!$lang) {
					$lang = $me->nls;
				}

				$vedorPath      = $me->path;
				$vedorUrl       = $me->make_local_url();
				$vedorParentUrl = $me->make_local_url('..');
				$vedorLanguage  = $lang;
				$vedorUrlNls    = $me->make_local_url("",$lang);
				$vedorSiteNls   = $me->make_local_url($me->currentsite(), $lang);

				
				$vedorNlsList = array();
				$config       = $me->loadConfig();
				foreach($config->nls->list as $nls => $lang) {
					$vedorNlsList[$me->make_local_url("",$nls).edit::getEditTemplate()] = $AR->nls->list[$nls];
				}

				echo "<script type='vedor/reset' data-vedor-path='$vedorPath' data-vedor-url='$vedorUrl' data-vedor-parent-url='$vedorParentUrl' ";
				echo "data-vedor-nls-list='" . json_encode($vedorNlsList) . "' data-vedor-language='$vedorLanguage' ";
				echo "data-vedor-url-nls='$vedorUrlNls' data-vedor-site-nls='$vedorSiteNls'>\n";
				echo "</script>";
			}
		}

		public static function init() {
			global $ARCurrent;
			if (edit::getEditMode()) {
				$context                = pobject::getContext();
				$me                     = $context["arCurrentObject"];

				$ARCurrent->nolangcheck = true;
				$ARCurrent->allnls      = true;
				$options                = $me->call("editor.ini");

				echo "<script type='vedor/editorSettings'>";
				echo json_encode($options);
				echo "</script>";
			}
		}

		public static function setEditMode($mode=false, $template='user.edit.page.html', $prefix="editable_") {
			global $mod_edit_data;
			$mod_edit_data['editmode']     = $mode;
			$mod_edit_data['edittemplate'] = $template;
			$mod_edit_data['editprefix']   = $prefix;
		}

		public static function getEditMode() {
			global $mod_edit_data;
			return $mod_edit_data['editmode']??null;
		}

		public static function getEditTemplate() {
			global $mod_edit_data;
			return $mod_edit_data['edittemplate']??null;
		}

		public static function getEditPrefix() {
			global $mod_edit_data;
			return $mod_edit_data['editprefix']??null;
		}

		public static function getEditTarget() {
			return '_self';
		}

		public static function registerDataField() {
			/* private method */
			global $mod_edit_data;
			$id     = ++$mod_edit_data['id'];
			return $id;
		}

		public static function getVedorVars($me, $name) {
			$vedorVars  = "data-vedor-path='" . $me->path . "' data-vedor-id='" . $me->id . "' data-vedor-field='" . $name . "'".
			              " ar:path='" . $me->path . "' ar:id='" . $me->id . "'";

			return $vedorVars;		
		}

		public static function showInputText($var, $name, $title='', $extra='') {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id        = edit::registerDataField();
				$prefix    = edit::getEditPrefix();
				$vedorVars = edit::getVedorVars($me, $name);

				echo "<input type='text' class='editable' id='".$prefix.$id."' $vedorVars title='$title' value=\"";
				echo htmlspecialchars($var);
				echo "\" $extra>";
			} else if (!edit::isEmpty($var)) {
				echo $var;
			}
			return $id;
		}

		public static function showInput($var, $name, $title, $type='text', $extra='') {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id        = edit::registerDataField();
				$prefix    = edit::getEditPrefix();
				$vedorVars = edit::getVedorVars($me, $name);

				echo "<input name='$name' type='$type' class='editable' id='".$prefix.$id."' $vedorVars title='$title' value=\"";
				echo htmlspecialchars($var);
				echo "\" $extra>";			
			} else if (!edit::isEmpty($var)) {
				echo $var;
			}
			return $id;
		}

		public static function registerGroup($name, $id) {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			/* private method - adds $id to group $name, a change in any member of the group, forces dirty on all members */
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$prefix = edit::getEditPrefix();
				echo "<script type='vedor/registerGroup' data-vedor-group='$name' data-vedor-id='$prefix$id'></script>\n";
			}
		}

		public static function showCheckbox($var, $name, $title, $extra='', $group='', $value='1' ) {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if(edit::getEditMode() && $me->CheckSilent('edit')) {
				$id=edit::registerDataField();
				if ($group) {
					edit::registerGroup($group, $id);
				}
				edit::ShowInput(0, $name, $title, 'hidden');
				$checked = "";
				if( $var == $value ) {
					$checked = "checked";
				}
				$prefix    = edit::getEditPrefix();
				$vedorVars = edit::getVedorVars($me, $name);
				echo "<input name='$name' type='checkbox' class='editable' id='".$prefix.$id."' $vedorVars title='$title' value='$value' $extra $checked>";
			} else if( !edit::isEmpty($var)) {
				echo $var;
			}
			return $id;
		}

		public static function showRadio($var, $name, $value, $title, $extra='' ) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id = edit::registerDataField();
				$checked = "";
				if( $var == $value ) {
					$checked = "checked";
				}
				$prefix = edit::getEditPrefix();
				$vedorVars = edit::getVedorVars($me, $name);

				echo "<input name='$name' type='radio' class='editable' id='".$prefix.$id."' $vedorVars title='$title' value=\"".htmlspecialchars($value)."\" $extra $checked>";
			} else if( !edit::isEmpty($var)) {
				echo $var;
			}
			return $id;
		}

		public static function showSelect($var, $name, $title, $list, $bykey=false, $extra='') {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id        = edit::registerDataField();
				$prefix    = edit::getEditPrefix();
				$vedorVars = edit::getVedorVars($me, $name);

				echo "<select class='editable' id='".$prefix.$id."' $vedorVars title='$title' $extra>";
				foreach ($list as $key => $value) {
					echo "<option";
					if ($bykey) {
						echo " value=\"$key\"";
						if (
							($key==$var) ||
							(is_array($var) && in_array($key, $var))
						) {
							echo " selected";
						}
					} else {
						echo " value=\"$value\"";
						if (
							($value==$var) ||
							(is_array($var) && in_array($value, $var))
						) {
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

		public static function fixSource($var) { // replace the fixed source code span with the fixed source code (base64encoded in vd:source)
			global $ARnls;

			if (
				preg_match('/<(span|div)[^>]*vd:cookieconsentrequired="true"[^>]*>.*<span.*vd:endsource="true".*>.*<\/span>.*<\/(span|div)>/isU', $var) &&
				ldGetUserCookie("ARCookieConsent") != true
			) {
				$var = preg_replace_callback(
					'/<(span|div)[^>]*vd:source="([^"]*)"[^>]*>.*<span.*vd:endsource="true".*>.*<\/span>.*<\/(span|div)>/isU',
					function($matches) use ($ARnls) {
						return "[" . $ARnls['vd_cookie_consent_required'] . "]";
					}, $var);
			} else {

				$var = preg_replace_callback(
					'/<(span|div)[^>]*vd:source="([^"]*)"[^>]*>.*<span.*vd:endsource="true".*>.*<\/span>.*<\/(span|div)>/isU',
					function($matches) {
						return base64_decode($matches[2]);
					}, $var);
			}
	
			return $var;
		}

		public static function fixEditSource($var) {
			$var = preg_replace_callback(
				'/(<(span|div)[^>]*vd:source=")([^"]*)("[^>]*>).*(<span[^>]*vd:endsource="true".*>.*<\/span>.*<\/(span|div)>)/isU',
				function($matches) {
					return $matches[1] . $matches[3] . $matches[4] . base64_decode($matches[3]) . $matches[5];
				}, $var);
	
			return $var;
		}
		
		public static function showSpan($var, $name, $title='', $extra='') {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id        = edit::registerDataField();
				$prefix    = edit::getEditPrefix();
				$vedorVars = edit::getVedorVars($me, $name);

				echo "<span class='editable' id='".$prefix.$id."' $vedorVars title='$title' $extra>";
				echo edit::fixEditSource(page::parse($var));
				echo "</span>";
			} else if (!edit::isEmpty($var)) {
				echo page::stripARNameSpace(edit::fixSource(page::parse($var)));
			}
			return $id??null;
		}

		public static function showTextSpan($var, $name, $title='', $extra='') {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id        = edit::registerDataField();
				$prefix    = edit::getEditPrefix();
				$vedorVars = edit::getVedorVars($me, $name);

				echo "<span class='editable text-only' id='".$prefix.$id."' $vedorVars title='$title' $extra>";
				echo page::parse($var);
				echo "</span>";
			} else if (!edit::isEmpty($var)) {
				echo page::parse($var);
			}
			return $id??null;
		}

		public static function showDiv($var, $name, $title='', $extra='') {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				$id        = edit::registerDataField();
				$prefix    = edit::getEditPrefix();
				$vedorVars = edit::getVedorVars($me, $name);

				echo "<div class='editable' id='".$prefix.$id."' $vedorVars title='$title' $extra>";
				echo edit::fixEditSource(page::parse($var));
				echo "</div>";
			} else if (!edit::isEmpty($var)) {
				echo page::stripARNameSpace(edit::fixSource(page::parse($var)));
			}
			return $id??null;
		}

		public static function startContainer() {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				echo "<span ar:type='container' ar:path='".$me->path."' ar:id='".$me->id."'>";
			}
		}

		public static function endContainer() {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (edit::getEditMode() && $me->CheckSilent('edit')) {
				echo "</span>";
			}
		}

		public static function showLink($path='', $extra='', $url=false, $localurl=false) { 
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (!$localurl) {
				$_url	= $me->make_url($path);
			} else {
				$_url	= $me->make_local_url($path);
			}
			if (edit::getEditMode()) {
				echo "<a onClick='parent.browseTo(this.href); return false;' href='".$_url.edit::getEditTemplate()."?vdLanguage=".$me->_getvar('vdLanguage')."' $extra target='".edit::getEditTarget()."'>";
			} else {
				if (!$url) {
					if ($_url) {
						$url = $_url;
					} else {
						$url = $me->make_url($path);
					}
				}
				echo "<a href='".$url."' $extra>";
			}
		}

		public static function showEditableLink($path='', $extra='', $url=false, $localurl=false) {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (!$localurl) {
				$_url	= $me->make_url($path);
			} else {
				$_url	= $me->make_local_url($path);
			}
			if (edit::getEditMode()) {
				echo "<a onClick=\"event.cancelBubble=true\" onDblClick=\"parent.browseTo('".$_url.edit::getEditTemplate()."?vdLanguage=".$me->_getvar('vdLanguage')."')\" $extra>";
			} else {
				if (!$url) {
					if ($_url) {
						$url = $_url;
					} else {
						$url = $me->make_url($path);
					}
				}
				echo "<a href='".$url."' $extra>";				
			}
		}
		
		public static function showHref($path='', $extra='', $localurl=false) {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (!$localurl) {
				$_url	= $me->make_url($path);
			} else {
				$_url	= $me->make_local_url($path);
			}
			if (edit::getEditMode()) {
				echo "href='".$_url.edit::getEditTemplate()."?vdLanguage=".$me->_getvar('vdLanguage')."' $extra target='".edit::getEditTarget()."'";
			} else {
				echo "href='".$_url."'";
			}
		}

        public static function showUrl($path='', $localurl=false) {
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			if (!$localurl) {
				$_url	= $me->make_url($path);
			} else {
				$_url	= $me->make_local_url($path);
			}
            if (edit::getEditMode()) {
                echo $_url.edit::getEditTemplate()."?vdLanguage=".$me->_getvar('vdLanguage');
            } else {
                echo $_url;
            }
        }

		public static function isEmpty($var) {
			if (strpos($var, 'vd:source')===false) {
				return trim(preg_replace('/&nbsp;/',' ',strip_tags($var, '<script><input><img><object><embed><iframe>')))=='';
			} else {
				return false;
			}
		}
	}

	class pinp_edit {

		public static function _reset() {
			return edit::reset();
		}

		public static function _init() {
			return edit::init();
		}

		public static function _setEditMode($mode=false, $template='user.edit.page.html', $prefix='editable_') {
			return edit::setEditMode($mode, $template, $prefix);
		}

		public static function _getEditMode() {
			return edit::getEditMode();
		}

		public static function _getEditTemplate() {
			return edit::getEditTemplate();
		}

		public static function _getEditPrefix() {
			return edit::getEditPrefix();
		}

		public static function _getEditTarget() {
			return edit::getEditTarget();
		}

		public static function _registerDataField($name) {
			$id      = edit::registerDataField();
			// FIXME: Temporary fix voor older code which still use registerDataField
			$context = pobject::getContext();
			$me      = $context["arCurrentObject"];
			$prefix  = edit::getEditPrefix();
			echo "<script> parent.registerDataField('".$prefix.$id."','".AddCSlashes($name, ARESCAPE)."','".$me->path."'
				,".$me->id."); </script>\n";
			return $id;

		}

		public static function _registerGroup($name, $id) {
			return edit::registerGroup($name, $id);
		}
		
		public static function _showInputText($var, $name, $title='', $extra='') {
			return edit::showInputText($var, $name, $title, $extra);
		}

		public static function _showInput($var, $name, $title, $type='text', $extra='') {
			return edit::showInput($var, $name, $title, $type, $extra);
		}

		public static function _showCheckbox($var, $name, $title, $extra='', $group='', $value='1' ) {
			return edit::showCheckbox($var, $name, $title, $extra, $group, $value );
		}

		public static function _showRadio($var, $name, $value, $title, $extra='' ) {
			return edit::showRadio($var, $name, $value, $title, $extra );
		}

		public static function _showSelect($var, $name, $title, $list, $bykey=false, $extra='') {
			return edit::showSelect($var, $name, $title, $list, $bykey, $extra);
		}

		public static function _showSpan($var, $name, $title='', $extra='') {
			return edit::showSpan($var, $name, $title, $extra);
		}

		public static function _showTextSpan($var, $name, $title='', $extra='') {
			return edit::showTextSpan($var, $name, $title, $extra);
		}

		public static function _showDiv($var, $name, $title='', $extra='') {
			return edit::showDiv($var, $name, $title, $extra);
		}

		public static function _startContainer() {
			return edit::startContainer();
		}

		public static function _endContainer() {
			return edit::endContainer();
		}

		public static function _showLink($path='', $extra='', $url=false, $localurl=false) {
			return edit::showLink($path, $extra, $url, $localurl);
		}

		public static function _showEditableLink($path='', $extra='', $url=false, $localurl=false) {
			return edit::showEditableLink($path, $extra, $url, $localurl);			
		}
		
		public static function _showHref($path='', $localurl='') {
			return edit::showHref($path, $localurl);
		}

		public static function _showUrl($path='', $localurl=false) {
			return edit::showUrl($path, $localurl);
		}

		public static function _isEmpty($var) {
			return edit::isEmpty($var);
		}
	}
?>
