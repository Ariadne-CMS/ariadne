<?php
	/*
		Generate a optionlist for use in a select area. The list is
		generated from the typetree settings.

		Takes a 'selected' argument which can be used to add 'selected' to
		the list. If the selected type is not available it will be added to
		the list, in case this type was available in the typetree before.
	*/
	$this->call('typetree.ini');
	asort($ARCurrent->arTypeNames);
	$haveselected = false;
	foreach ($ARCurrent->arTypeNames as $typeValue => $typeName) {
		if (($selected??null) == $typeValue) {
			$selected_option = " selected";
			$haveselected = true;
		} else {
			$selected_option = "";
		}
		echo "<option value=\"$typeValue\"$selected_option>$typeName</option>\n";
	}
	if (!$haveselected && ($selected??null)) {
		// The template is not in the typetree anymore. This makes it available in the list.
		echo "<option value=\"$selected\" selected>$selected</option>\n";
	}
?>
