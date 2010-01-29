<?php
	$ARCurrent->nolangcheck=true;
  	include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);
  	
  	if( !function_exists("section_table") ) {
		function section_table($info) {
			global $ARnls;
			$result = '';
			if (is_array($info)) {
				$result .= '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
				foreach ($info as $key => $value) {
					$result .= '<tr><td valign="top">';
					$result .= $ARnls[$key] . ":";
					$result .= '</td><td valign="top" class="data">';
					$result .= $value;
					$result .= '</td></tr>';
				}
				$result .= '</table>';
			}
			return $result;
		}
	}
	
	if( !function_exists("labelspan") ) {
		function labelspan($label, $maxlabellength=16) {
			// Reduce length of a label if they are too long.
			if (mb_strlen($label, "utf-8") > $maxlabellength) {
				$origval = htmlspecialchars($label);
				$label = "<span title=\"$origval\">".htmlspecialchars(mb_substr($label, 0, $maxlabellength-3,"utf-8")."...")."</span>";
			} else {
				$label = htmlspecialchars($label);
			}
			return $label;
		}
	}
	
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		if (!$arLanguage) {
			$arLanguage=$nls;
			if (is_array($arCallArgs)) {
				$arCallArgs["arLanguage"]=$nls;
			} else {
				$arCallArgs.="&arLanguage=$nls";
			}
		}

		// FIXME: should take this information from sys.object.list.entry.html

		$myType = $this->get("/system/ariadne/types/".$this->type, "system.get.name.phtml");
		if (!$myType) {
			$myType = array($this->type);
		}

		$info = array();
		$info['type'] = labelspan($myType[0]);
		$info['size'] = $this->size;

		$info['priority'] = $this->priority;
		if ($this->CheckSilent("edit")) {
			$info['priority'] = "<a href=\"javascript:muze.ariadne.explore.arshow('edit_priority','" . $this->make_local_url() . "dialog.priority.php')\" title=\"". $ARnls['change_priority'] . "\">" . $this->priority . "</a>";
		}

		$info['ariadne:id'] = $this->id;

/*		$details = '<strong>' . $myName . '</strong><br>';
		$details .= $myType[0];
		$details .= '<br>';
*/

		$details .= section_table($info);
		$section = array(
			'id' => 'info',
			'label' => $ARnls['ariadne:info'],
			'details' => $details
		);

		echo showSection($section);
	}
?>